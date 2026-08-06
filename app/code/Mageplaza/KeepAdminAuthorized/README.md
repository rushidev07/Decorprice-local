Magento 2 Keep Admin Authorized by Mageplaza
===========================================

####Support
    v1.0.4 - Magento 2.3.* - 2.4.*

####Change list
    v1.0.4 - Added Magento 2.4 compatibility
    v1.0.3 - Added Magento 2.3 compatibility
    v1.0.1 - Magento 2.2 compatibility checking (updated composer.json) 
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageplaza/KeepAdminAuthorized'.
    3. Unzip the content of archive to directory 'Magento_Root/app/code/Mageplaza/KeepAdminAuthorized'
       (use command 'unzip ArchiveName.zip -d path_to/app/code/Mageplaza/KeepAdminAuthorized').
    4. Run the command 'php bin/magento module:enable Mageplaza_KeepAdminAuthorized' in Magento root.
       If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageplaza_KeepAdminAuthorized'.
    5. Run the command 'php bin/magento setup:upgrade' in Magento root.
    6. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, 
       or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    7. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'
    8. Deploy static content: 'php bin/magento setup:static-content:deploy'