var config = {
    map: {
        '*': {
            'formValidator' : 'Amasty_CompanyAccount/js/formValidator',
            'amcompanyPrompt' : 'Amasty_CompanyAccount/js/prompt',
            'emailValidator' : 'Amasty_CompanyAccount/js/form/element/emailValidator',
            'amaclTree': 'Amasty_CompanyAccount/js/lib/jstree',
            'aclTree': 'Amasty_CompanyAccount/js/acl-tree',
            'amcompanyRegionUpdater': 'Amasty_CompanyAccount/js/region-updater'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Amasty_CompanyAccount/js/validation': true
            }
        }
    }
};
