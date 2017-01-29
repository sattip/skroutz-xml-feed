<?php
/**
 * Options.php description
 *
 * @author    Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @date      2015-12-27
 * @since     TODO ${VERSION}
 * @package   Pan\SkroutzXML
 * @copyright Copyright (c) 2015 Panagiotis Vagenas
 */

namespace Pan\SkroutzXML;

use Pan\MenuPages\Twig;
use Pan\XmlGenerator\Logger\Handlers\DBHandler;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class Options
 *
 * @author    Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @date      2015-12-27
 * @since     TODO ${VERSION}
 * @package   Pan\SkroutzXML
 * @copyright Copyright (c) 2015 Panagiotis Vagenas
 */
class Options extends \Pan\MenuPages\Options {
    const OPTIONS_NAME = 'skroutz__options';
    protected $twig;
    protected $pageHookSuffix;
    protected $pageId = 'skroutz-xml-settings';

    /**
     * @var array Availability options for skroutz.gr
     */
    public static $availOptions
        = array(
            'Άμεση παραλαβή / Παράδοση σε 1-3 ημέρες',
            'Παράδοση σε 1-3 ημέρες',
            'Παραλαβή από το κατάστημα ή Παράδοση, σε 1-3 ημέρες',
            'Παραλαβή από το κατάστημα ή Παράδοση, σε 4-10 ημέρες',
            'Παράδοση σε 4-10 ημέρες',
            'Κατόπιν παραγγελίας, παραλαβή ή παράδοση έως 30 ημέρες',
        );

    protected $fieldMap
        = [
            'id'           => 'id',
            'mpn'          => 'mpn',
            'name'         => 'name',
            'link'         => 'link',
            'image'        => 'image',
            'category'     => 'category',
            'price'        => 'price_with_vat',
            'inStock'      => 'instock',
            'availability' => 'availability',
            'manufacturer' => 'manufacturer',
            'color'        => 'color',
            'size'         => 'size',
            'isbn'         => 'isbn',
            'weight'       => 'weight'
        ];

    protected $fieldLengths
        = [
            'id'           => 200,
            'name'         => 300,
            'link'         => 1000,
            'image'        => 400,
            'category'     => 250,
            'price'        => 0,
            'inStock'      => 0,
            'availability' => 60,
            'manufacturer' => 100,
            'mpn'          => 80,
            'isbn'         => 80,
            'size'         => 500,
            'color'        => 100,
            'weight'       => 0,
        ];
    protected $requiredFields
        = [
            'id',
            'name',
            'link',
            'image',
            'category',
            'price',
            'inStock',
            'availability',
            'manufacturer',
            'mpn',
        ];

    public function __construct( $optionsBaseName, array $defaults ) {
        parent::__construct( $optionsBaseName, $defaults );
    }

    /**
     * @param string $optionsBaseName
     * @param array  $defaults
     *
     * @return \Pan\MenuPages\Options|Options
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     */
    public static function getInstance( $optionsBaseName = '', array $defaults = [] ) {
        return parent::getInstance( self::OPTIONS_NAME, self::getDefaultsArray() );
    }

    public function getDefaults() {
        if ( ! $this->defaults ) {
            $this->defaults = self::getDefaultsArray();
        }

        return parent::getDefaults();
    }

    public static function getDefaultsArray() {
        return [
            'donate'                 => 1,
            /*********************
             * XML File relative
             ********************/
            // File location
            'xml_location'           => '',
            // File name
            'xml_fileName'           => 'skroutz.xml',
            // Generation interval
            'xml_interval'           => 86399, // TODO Changed to int from 151228
            // XML Generate Request Var
            'xml_generate_var'       => 'skroutz',
            // XML Generate Request Var Value
            'xml_generate_var_value' => self::generatePassword( 32 ),
            /*********************
             * Products relative
             ********************/
            // Include products
            'products_include'       => [],
            // Availability when products in stock
            'avail_inStock'          => 0,
            // Availability when products out stock
            'avail_outOfStock'       => 6,
            // Availability when products out stock and backorders are allowed
            'avail_backorders'       => 6,
            // Exclude products
            'ex_cats'                => [],
            'ex_tags'                => [],
            /*********************
             * Custom fields
             ********************/
            'map_id'                 => 0,
            'map_name'               => 0,
            'map_name_append_sku'    => 0,
            'map_link'               => 0, // TODO Deprecated since 151228
            'map_image'              => '3', // TODO Need translation for the new version
            'map_category'           => 'product_cat',
            'map_category_tree'      => 0,
            'map_price_with_vat'     => 1,
            'map_manufacturer'       => 'product_cat',
            'map_mpn'                => 0,
            'map_size'               => array(),
            'map_size_use'           => 0, // TODO Deprecated since 151228
            'map_color'              => array(),
            'map_color_use'          => 0, // TODO Deprecated since 151228
            /***********************************************
             * Fashion store
             ***********************************************/
            'is_fashion_store'       => 0,
            /***********************************************
             * ISBN
             ***********************************************/
            'map_isbn'               => 0,
            'is_book_store'          => 0,
        ];
    }

    public function translateOptions() {
        return [
            'mapId'            => $this->get( 'map_id' ),
            'mapMpn'           => $this->get( 'map_mpn' ),
            'mapName'          => $this->get( 'map_name' ),
            'mapNameAppendSku' => $this->get( 'map_name_append_sku' ),
            'mapImage'         => $this->get( 'map_image' ),
            'mapCategory'      => $this->get( 'map_category' ),
            'mapCategoryTree'  => $this->get( 'map_category_tree' ),
            'mapPrice'         => $this->get( 'map_price_with_vat' ),
            'mapManufacturer'  => $this->get( 'map_manufacturer' ),
            'mapColor'         => $this->get( 'map_color' ),
            'mapSize'          => $this->get( 'map_size' ),
            'mapIsbn'          => $this->get( 'map_isbn' ),
            'fashionStore'     => $this->get( 'is_fashion_store' ),
            'bookStore'        => $this->get( 'is_book_store' ),
            'exclCategories'   => $this->get( 'ex_cats' ),
            'exclTags'         => $this->get( 'ex_tags' ),
        ];
    }

    public function getFileLocationOption() {
        return trailingslashit( ABSPATH ) . $this->getXmlRelLocationOption();
    }

    public function getXmlRelLocationOption() {
        return ( $this->get( 'xml_location' ) ? trailingslashit( $this->get( 'xml_location' ) ) : '' )
               . $this->get( 'xml_fileName' );
    }

    public function getCreatedAtOption() {
        return 'created_at';
    }

    public function getXmlRootElemName() {
        return 'mywebstore';
    }

    public function getXmlProductsElemWrapper() {
        return 'products';
    }

    public function getXmlProductElemName() {
        return 'product';
    }

    /**
     * @return array
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @see    Options::$fieldMap
     * @codeCoverageIgnore
     */
    public function getFieldMap() {
        return $this->fieldMap;
    }

    /**
     * @return array
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @see    Options::$fieldLengths
     * @codeCoverageIgnore
     */
    public function getFieldLengths() {
        return $this->fieldLengths;
    }

    /**
     * @return array
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @see    Options::$requiredFields
     * @codeCoverageIgnore
     */
    public function getRequiredFields() {
        return $this->requiredFields;
    }

    public function getGenerateXmlUrl() {
        return home_url() . "?{$this->get('xml_generate_var')}={$this->get('xml_generate_var_value')}";
    }

    public function addMenuPages() {
        //create new top-level menu
        $this->pageHookSuffix = add_submenu_page( 'options-general.php',
                                                  'Skroutz XML Feed Settings',
                                                  'Skroutz XML Feed',
                                                  'administrator',
                          $this->optionsBaseName . '-settings',
                                                  [ $this, 'renderSettingsPage' ] );

        //call register settings function
        add_action( 'admin_init', [ $this, 'registerSettings' ] );
        add_action( "admin_footer-{$this->pageHookSuffix}", [$this, 'footerScripts'] );
    }

    /**
     * TODO Move this to JS file
     */
    public function footerScripts(){
        return;
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                postboxes.add_postbox_toggles( '<?php echo esc_js($this->pageHookSuffix); ?>' );
                $('#fx-smb-form').submit( function(){
                    $('#publishing-action').find('.spinner').css('display','inline');
                });
                $('#delete-action').find('.submitdelete').on('click', function() {
                    return confirm(/*'Are you sure want to do this?'*/'Sorry, not yet implemented');
                });
            });
        </script>
        <?php
    }

    public function getPageId(){
        return $this->pageId;
    }

    public function registerSettings() {
        register_setting( $this->optionsBaseName . '-settings-group',
                          $this->optionsBaseName,
                          [ $this, 'validateSettings' ] );
    }

    public function validateSettings( $value ) {
        // FIXME Validate settings
        return $value;
    }

    public function saveBox(){
        ?>
        <div id="submitpost" class="submitbox">

            <div>

                <div id="delete-action">
                    <a href="#" class="submitdelete deletion">Reset Settings</a>
                </div>

                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php submit_button( esc_attr( 'Save' ), 'primary', 'submit', false );?>
                </div>

                <div class="clear"></div>

            </div>

        </div>

        <?php
    }

    public function infoBox(){
        $skz      = new Skroutz();
        $fileInfo = $skz->getXmlObj()->getFileInfo();

        $genUrl = Options::getInstance()->getGenerateXmlUrl();

        $content = '<div class="row">';
        if ( empty( $fileInfo ) ) {
            $content .= '<p class="alert alert-danger">
                        File not generated yet. Please use the <i>Generate XML Now</i>
                        button to generate a new XML file</p>';
        } else {
            foreach ( $fileInfo as $item ) {
                $content .= '<span class="list-group-item">';
                $content .= $item['label'] . ': </br><strong>' . $item['value'] . '</strong>';
                $content .= '</span><hr>';
            }

            $content .= '<p><a class="button button-primary button-sm action-button" href="'
                        . home_url( Options::getInstance()->getXmlRelLocationOption() )
                        . '" target="_blank" role="button">';
            $content .= 'Open Cached File';
            $content .= '</a></p><hr>';
            $content .= '<p><a class="button button-primary button-sm action-button" href="'
                        . $genUrl
                        . '" target="_blank" role="button">';
            $content .= 'Open Generate URL';
            $content .= '</a></p>';
        }
        $content .= '</div>';

        echo $content;
    }

    public function genNowBox() {
        ?>
        <p>
            <button id="generate"
                    title="Generate XML Now"
                    class="button button-hero button-controls gen-now-button widefat"
                    data-target="#generateNowModal"
                    data-toggle="xd-v141226-dev-modal">
                <i class="fa fa-cog fa-spin fa-fw" style=" display: none;"></i>
                Generate XML Now
            </button>
        </p>
        <?php
        wp_nonce_field('skz-gen-now-action', 'skz-gen-now-action');
    }

    public function generalOptionsBox(){
        $availOptions = [];
        foreach ( Options::$availOptions as $value => $label ) {
            $availOptions[ (string) $value ] = $label;
        }

        $availOptionsDoNotInclude                   = $availOptions;
        $availOptionsDoNotInclude[] = 'Do not Include';

        $attrTaxonomies = [];
        foreach ( wc_get_attribute_taxonomies() as $atrTax ) {
            $attrTaxonomies[ $atrTax->attribute_id ] = $atrTax->attribute_label;
        }

        $productCategories = get_categories( [ 'taxonomy' => 'product_cat', 'hide_empty' => 0 ] );
        $categories = [];
        foreach ( $productCategories as $productCategory ) {
            $categories[(string)$productCategory->term_id] = $productCategory->name;
        }

        $productTags = get_terms( ['taxonomy' => 'product_tag', 'hide_empty' => 0] );
        $tags = [];
        foreach ( $productTags as $productTag ) {
            $tags[(string)$productTag->term_id] = $productTag->name;
        }

        $genUrl = Options::getInstance()->getGenerateXmlUrl();
        ?>
        <table class="form-table general-options">
            <span class="list-group-item">Generate XML URL:
                <strong><?php echo esc_html($genUrl); ?></strong>
            </span>
            <hr>

            <?php

            echo $this->getTwig()->loadAndRender(
                'text',
                [
                    'id' => 'xml_generate_var',
                    'name' => $this->getOptionInputName( 'xml_generate_var' ),
                    'value' => $this->get( 'xml_generate_var' ),
                    'label' => 'XML Request Generate Variable',
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'text',
                [
                    'id' => 'xml_generate_var_value',
                    'name' => $this->getOptionInputName( 'xml_generate_var_value' ),
                    'value' => $this->get( 'xml_generate_var_value' ),
                    'label' => 'XML Request Generate Variable Value',
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'text',
                [
                    'id' => 'xml_fileName',
                    'name' => $this->getOptionInputName( 'xml_fileName' ),
                    'value' => $this->get( 'xml_fileName' ),
                    'label' => 'XML Filename',
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'text',
                [
                    'id' => 'xml_location',
                    'name' => $this->getOptionInputName( 'xml_location' ),
                    'value' => $this->get( 'xml_location' ),
                    'label' => 'XML File Location',
                ]
            );

            $intervalOptions = [
                'daily' => 'Daily',
                'twicedaily' => 'Twice Daily',
                'hourly' => 'Hourly',
                'every30m' => 'Every Thirty Minutes',
                '0' => 'Disable caching of file (not recommended for stores with many products)',
            ];
            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'xml_interval',
                    'name' => $this->getOptionInputName( 'xml_interval' ),
                    'label' => 'XML File Generation Interval',
                    'options' => $intervalOptions,
                    'selected' => $this->get( 'xml_interval' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'avail_inStock',
                    'name' => $this->getOptionInputName( 'avail_inStock' ),
                    'label' => 'Product availability when item is in stock',
                    'options' => $availOptions,
                    'selected' => $this->get( 'avail_inStock' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'avail_outOfStock',
                    'name' => $this->getOptionInputName( 'avail_outOfStock' ),
                    'label' => 'Product availability when item is out of stock',
                    'options' => $availOptionsDoNotInclude,
                    'selected' => $this->get( 'avail_outOfStock' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'avail_backorders',
                    'name' => $this->getOptionInputName( 'avail_backorders' ),
                    'label' => 'Product availability when item is out of stock and backorders are allowed',
                    'options' => $availOptionsDoNotInclude,
                    'selected' => $this->get( 'avail_backorders' ),
                ]
            );

            if($categories){
                echo $this->getTwig()->loadAndRender(
                    'select',
                    [
                        'id' => 'ex_cats',
                        'name' => $this->getOptionInputName( 'ex_cats' ),
                        'label' => 'Exclude products from certain categories (if a product has any of these categories then it will not be included in the XML)',
                        'options' => $categories,
                        'selected' => $this->get( 'ex_cats' ),
                        'multiple' => true,
                    ]
                );
            }

            if($tags){
                echo $this->getTwig()->loadAndRender(
                    'select',
                    [
                        'id' => 'ex_tags',
                        'name' => $this->getOptionInputName( 'ex_tags' ),
                        'label' => 'Exclude products from certain tags (if a product has any of these tags then it will not be included in the XML)',
                        'options' => $tags,
                        'selected' => $this->get( 'ex_tags' ),
                        'multiple' => true,
                    ]
                );
            }
            ?>
        </table>
        <?php
    }

    public function mapOptionsBox(){
        $productCategories = get_categories( [ 'taxonomy' => 'product_cat', 'hide_empty' => 0 ] );
        $categories = [];
        foreach ( $productCategories as $productCategory ) {
            $categories[] = [ 'label' => $productCategory->name, 'value' => (string) $productCategory->term_id ];
        }

        $productTags = get_terms( ['taxonomy' => 'product_tag', 'hide_empty' => 0] );
        $tags = [];
        foreach ( $productTags as $productTag ) {
            $tags[] = [ 'label' => $productTag->name, 'value' => (string) $productTag->term_id ];
        }
        $attrTaxonomies = wc_get_attribute_taxonomies();
        ?>
        <table class="form-table map-fields">
            <tr valign="top">
                <th scope="row"><label for="map_id">Product ID</label></th>
                <td>
                    <select id="map_id"
                            name="<?php echo $this->getOptionInputName( 'map_id' ); ?>">
                        <option value="0" <?php echo selected('0', $this->get( 'map_id' )); ?>>Use Product SKU</option>
                        <option value="1" <?php echo selected('1', $this->get( 'map_id' )); ?>>Use Product ID</option>
                    </select>
                </td>
            </tr>

            <?php
            $options = array();

            $options['product_cat'] = 'Use Product Categories';
            $options['product_tag'] = 'Use Product Tags';

            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            if ( Skroutz::hasBrandsPlugin() && ( $brandsTax = Skroutz::getBrandsPluginTaxonomy() ) ) {
                $options[$brandsTax->name] = 'Use WooCommerce Brands Plugin';
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_id',
                    'name' => $this->getOptionInputName( 'map_id' ),
                    'label' => 'Product Manufacturer Field',
                    'options' => $options,
                    'selected' => $this->get( 'map_id' ),
                ]
            );

            $options = array();

            $options['0'] = 'Use Product SKU';
            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_mpn',
                    'name' => $this->getOptionInputName( 'map_mpn' ),
                    'label' => 'Product Manufacturer SKU',
                    'options' => $options,
                    'selected' => $this->get( 'map_mpn' ),
                ]
            );

            $options = array();

            $options['0'] = 'Use Product Name';

            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_name',
                    'name' => $this->getOptionInputName( 'map_name' ),
                    'label' => 'Product Name',
                    'options' => $options,
                    'selected' => $this->get( 'map_name' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'checkbox',
                [
                    'id' => 'map_name_append_sku',
                    'name' => $this->getOptionInputName( 'map_name_append_sku' ),
                    'label' => 'If you check this the product SKU will be appended to product name. If no SKU is set then product ID will be used.',
                    'checked' => checked( '1', $this->get( 'map_name_append_sku' ), false ),
                ]
            );

            $options = [
                '0' => 'Thumbnail',
                '1' => 'Medium',
                '2' => 'Large',
                '3' => 'Full',
            ];

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_image',
                    'name' => $this->getOptionInputName( 'map_image' ),
                    'label' => 'Product Image',
                    'options' => $options,
                    'selected' => $this->get( 'map_image' ),
                ]
            );

            $options = [
                '0' => 'Use Product Permalink'
            ];

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_link',
                    'name' => $this->getOptionInputName( 'map_link' ),
                    'label' => 'Product Link',
                    'options' => $options,
                    'selected' => $this->get( 'map_link' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_price_with_vat',
                    'name' => $this->getOptionInputName( 'map_price_with_vat' ),
                    'label' => 'Product Price',
                    'options' => [
                        '0' => 'Regular Price',
                        '1' => 'Sales Price',
                        '2' => 'Price Without Tax',
                    ],
                    'selected' => $this->get( 'map_price_with_vat' ),
                ]
            );

            $options = [
                'product_cat' => 'Categories',
                'product_tag' => 'Tags',
            ];

            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_category',
                    'name' => $this->getOptionInputName( 'map_category' ),
                    'label' => 'Product Categories',
                    'options' => $options,
                    'selected' => $this->get( 'map_category' ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'checkbox',
                [
                    'id' => 'map_category_tree',
                    'name' => $this->getOptionInputName( 'map_category_tree' ),
                    'label' => 'Include full path to product category',
                    'checked' => checked( '1', $this->get( 'map_category_tree' ), false ),
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'checkbox',
                [
                    'id' => 'is_fashion_store',
                    'name' => $this->getOptionInputName( 'is_fashion_store' ),
                    'label' => 'This Store Contains Fashion Products',
                    'checked' => checked( '1', $this->get( 'is_fashion_store' ), false ),
                ]
            );

            $options = array();

            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_size',
                    'name' => $this->getOptionInputName( 'map_size' ),
                    'label' => 'Product Sizes',
                    'options' => $options,
                    'selected' => $this->get( 'map_size' ),
                    'multiple' => true,
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_color',
                    'name' => $this->getOptionInputName( 'map_color' ),
                    'label' => 'Product Colors',
                    'options' => $options,
                    'selected' => $this->get( 'map_color' ),
                    'multiple' => true,
                ]
            );

            echo $this->getTwig()->loadAndRender(
                'checkbox',
                [
                    'id' => 'is_book_store',
                    'name' => $this->getOptionInputName( 'is_book_store' ),
                    'label' => 'This is a Bookstore',
                    'checked' => checked( '1', $this->get( 'is_book_store' ), false ),
                ]
            );

            $options = [
                '0' => 'Use Product SKU',
            ];

            foreach ( $attrTaxonomies as $taxonomies ) {
                $options[$taxonomies->attribute_id] = $taxonomies->attribute_label;
            }

            echo $this->getTwig()->loadAndRender(
                'select',
                [
                    'id' => 'map_isbn',
                    'name' => $this->getOptionInputName( 'map_isbn' ),
                    'label' => 'ISBN',
                    'options' => $options,
                    'selected' => $this->get( 'map_isbn' ),
                ]
            );
            ?>
        </table>
        <?php
    }

    public function logsBox(){
        echo DBHandler::getLogMarkUp(Skroutz::DB_LOG_NAME);
    }

    public function renderSettingsPage() {
        /* global vars */
        global $hook_suffix;

        /* enable add_meta_boxes function in this page. */
        do_action( 'add_meta_boxes', $hook_suffix );

        ?>

        <div class="wrap">
            <h2>Skroutz XML Feed Settings</h2>
            <div class="fx-settings-meta-box-wrap">
                <form method="post" action="options.php">
                    <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                    <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
                    <?php settings_fields( $this->optionsBaseName . '-settings-group' ); ?>
                    <?php do_settings_sections( $this->optionsBaseName . '-settings-group' ); ?>
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                            <div id="postbox-container-1" class="postbox-container">
                                <?php do_meta_boxes( $hook_suffix, 'side', null ); ?>
                            </div>
                            <div id="postbox-container-2" class="postbox-container">
                                <?php do_meta_boxes( $hook_suffix, 'main', null ); ?>
                            </div>
                        </div>
                        <br class="clear">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    protected function getOptionInputName( $optionName ) {
        return esc_attr("{$this->optionsBaseName}[{$optionName}]");
    }

    /**
     * @return mixed
     */
    public function getPageHookSuffix() {
        return $this->pageHookSuffix;
    }
    public static function generatePassword( $length = 32 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = '';
        for ( $i = 0; $i < $length; $i ++ ) {
            $password .= substr( $chars, mt_rand( 0, strlen( $chars ) - 1 ), 1 );
        }

        return $password;
    }

    protected function getTwig(){
        if(!$this->twig){
            $this->twig = new Twig(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'templates');
        }

        return $this->twig;
    }
}