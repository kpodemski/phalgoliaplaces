<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
/**
* @author Krystian Podemski <krystian@prestahome.com>
* @copyright  (c) 2016 Krystian Podemski - www.podemski.info / www.PrestaHome.com
* @license    You only can use module, nothing more!
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhAlgoliaPlaces extends Module
{
    private $options_prefix;
    private $fields_form = array();
    private $hooksToInstall = array(
        'displayHeader',
    );

    public function __construct()
    {
        $this->name = 'phalgoliaplaces';
        $this->tab = 'front_office_features';
        $this->version = '0.4.0';
        $this->author = 'PrestaHome';
        $this->need_instance = 0;
        $this->is_configurable = 0;
        $this->ps_versions_compliancy['min'] = '1.6';
        $this->ps_versions_compliancy['max'] = _PS_VERSION_;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        
        parent::__construct();

        $this->displayName = $this->l('Integration with Algolia Places');
        $this->description = $this->l('Algolia Places provides a fast, distributed and easy way to use address search autocomplete JavaScript library on your website. ');

        $this->options_prefix = $this->name.'_';
    }

    public function install()
    {
        $this->_clearCache('*');
        $this->renderConfigurationForm();
        $this->batchUpdateConfigs();

        // Hooks & Install
        return (parent::install()
                && $this->prepareModuleSettings()
                && $this->registerHook($this->hooksToInstall));
    }

    public function prepareModuleSettings()
    {
        $sql = array();
        $sql_file = _PS_MODULE_DIR_.$this->name.'/init/install_sql.php';
        if (file_exists($sql_file)) {
            include($sql_file);
            foreach ($sql as $s) {
                if (!Db::getInstance()->Execute($s)) {
                    die('Error while creating DB');
                }
            }
        }

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/init/my-install.php')) {
            include_once _PS_MODULE_DIR_.$this->name.'/init/my-install.php';
        }

        return true;
    }

    public function uninstall()
    {
        $this->renderConfigurationForm();
        $this->deleteConfigs();
        $this->_clearCache('*');

        if (!parent::uninstall()) {
            return false;
        }

        // Database
        $sql = array();
        $sql_file = _PS_MODULE_DIR_.$this->name.'/init/uninstall_sql.php';
        if (file_exists($sql_file)) {
            include($sql_file);
            foreach ($sql as $s) {
                if (!Db::getInstance()->Execute($s)) {
                    die('Error while creating DB');
                }
            }
        }

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/init/my-uninstall.php')) {
            include_once _PS_MODULE_DIR_.$this->name.'/init/my-uninstall.php';
        }

        return true;
    }

    public function getContent()
    {
        $this->context->controller->addjQueryPlugin(array(
            'fancybox'
        ));

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/views/js/admin.js')) {
            $this->context->controller->addJS(array(
                _MODULE_DIR_.$this->name.'/views/js/admin.js',
            ));
        }

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/views/css/admin.css')) {
            $this->context->controller->addCSS(array(
                _MODULE_DIR_.$this->name.'/views/css/admin.css',
            ));
        }

        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (Tools::isSubmit('save'.$this->name)) {
            $this->renderConfigurationForm();
            $this->batchUpdateConfigs();

            $this->_clearCache('*');
            $this->_html .= $this->displayConfirmation($this->l('Settings updated successfully.'));

        }
        return $this->_html . $this->renderForm();
    }

    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->renderConfigurationForm();

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
        }

        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'save'.$this->name;
        $helper->toolbar_btn =  array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            )
        );
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'link' => $this->context->link,
        );

        return $helper->generateForm($this->fields_form);
    }

    public function renderConfigurationForm()
    {
        if ($this->fields_form) {
            return;
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),

                'input' => array(
                    array(
                        'type'  => 'text',
                        'label' => $this->l('App Id:'),
                        'name'  => $this->options_prefix.'appid',
                        'default' => '',
                        'lang' => false,
                    ),

                    array(
                        'type'  => 'text',
                        'label' => $this->l('API key:'),
                        'name'  => $this->options_prefix.'apikey',
                        'default' => '',
                        'lang' => false,
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default'
                )
            ),
        );

        $this->fields_form[] = $fields_form;
    }

    public function getConfigFieldsValues()
    {
        $id_shop = Shop::getContextShopID(true);
        $id_shop_group = Shop::getContextShopGroupID(true);

        $fields_values = array();
        foreach ($this->fields_form as $k => $f) {
            foreach ($f['form']['input'] as $i => $input) {
                if (isset($input['ignore']) && $input['ignore'] == true) {
                    continue;
                }

                if (isset($input['lang']) && $input['lang'] == true) {
                    foreach (Language::getLanguages(false) as $lang) {
                        $values = Tools::getValue($input['name'].'_'.$lang['id_lang'], (Configuration::hasKey($input['name'], $lang['id_lang']) ? Configuration::get($input['name'], $lang['id_lang'], (int)$id_shop_group, (int)$id_shop) : $input['default']));
                        $fields_values[$input['name']][$lang['id_lang']] = $values;
                    }
                } else {
                    $values = Tools::getValue($input['name'], (Configuration::hasKey($input['name'], null, (int)$id_shop_group, (int)$id_shop) ? Configuration::get($input['name']) : $input['default']));
                    $fields_values[$input['name']] = $values;
                }
            }
        }

        $this->assignCustomConfigs($fields_values);

        return $fields_values;
    }

    public function batchUpdateConfigs()
    {
        foreach ($this->fields_form as $k => $f) {
            foreach ($f['form']['input'] as $i => $input) {
                if (isset($input['ignore']) && $input['ignore'] == true) {
                    continue;
                }

                if (isset($input['lang']) && $input['lang'] == true) {
                    $data = array();
                    foreach (Language::getLanguages(false) as $lang) {
                        $val = Tools::getValue($input['name'].'_'.$lang['id_lang'], $input['default']);
                        $data[$lang['id_lang']] = $val;
                    }
                    Configuration::updateValue(trim($input['name']), $data, true);
                } else {
                    $val = Tools::getValue($input['name'], $input['default']);
                    Configuration::updateValue($input['name'], $val, true);
                }
            }
        }

        $this->batchUpdateCustomConfigs();
    }

    public function deleteConfigs()
    {
        foreach ($this->fields_form as $k => $f) {
            foreach ($f['form']['input'] as $i => $input) {
                if (isset($input['ignore']) && $input['ignore'] == true) {
                    continue;
                }
                Configuration::deleteByName($input['name']);
            }
        }

        $this->deleteCustomConfigs();

        return true;
    }

    public function assignCustomConfigs(&$fields_values)
    {
        return array();
    }

    public function batchUpdateCustomConfigs()
    {
        return true;
    }

    public function deleteCustomConfigs()
    {
        return true;
    }

    /**
     * Return value with every available language
     * @param  string Value
     * @return array
     */
    public static function prepareValueForLangs($value)
    {
        $output = array();

        foreach (Language::getLanguages(false) as $lang) {
            $output[$lang['id_lang']] = $value;
        }

        return $output;
    }

    public function hookDisplayHeader($params)
    {
        $pages = array('authentication', 'address', 'order-opc');

        if (isset($this->context->controller->php_self) && in_array($this->context->controller->php_self, $pages)) {
            Media::addJsDef(
                array(
                    $this->name.'_appId' => Configuration::get($this->options_prefix.'appid'),
                    $this->name.'_apiKey' => Configuration::get($this->options_prefix.'appkey'),
                    $this->name.'_isoLang' => $this->context->language->iso_code,
                )
            );

            $this->context->controller->addCSS(array(
                _PS_MODULE_DIR_.$this->name.'/views/css/front.css',
            ));

            $this->context->controller->addJS(array(
                'https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js',
                'https://cdn.jsdelivr.net/places.js/1/places.min.js',
                _PS_MODULE_DIR_.$this->name.'/views/js/front.js',
            ));
        }
    }
}
