### Installing Amasty B2B Company Account for Magento 2 Community Edition

1. Navigate to https://amasty.com/amcustomer/account/products/
2. Copy the Public and Private keys in the 'Access Keys section'
3. When installing the repository below, you will be asked for a username and password. Use the public key as the username, and the private key as the password.


To download and install Magento modules, the vendor repository is required. The repository is a Composer path to the storage with Amasty extensions. Without the repository, Composer won’t be able to locate and download the requested package.

The Amasty repository can be added with the next command:

```composer config repositories.amasty composer "https://composer.amasty.com/community/" ```


Install the amasty package with:
``` composer require amasty/module-company-account	```

### Troubleshooting
##### #1 Error when importing database from the mysql dump file

> ERROR 1273 (HY000) at line 12160: Unknown collation: 'utf8mb4_0900_ai_ci'

##### 1.1. Check the current mysql collation and chars with:
`grep -r " DEFAULT CHARSET=" store-2022-04-27.sql  | grep COLLATE`
Result:
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
...
You now know the not matching sql collate and charset.
##### 1.2. Check your mysql collation and chars with the following commands in your database console:
``` MariaDB [magento]> SHOW VARIABLES LIKE '%char%';```
MariaDB [magento]> SHOW VARIABLES LIKE '%char%';
+--------------------------+----------------------------+
| Variable_name            | Value                      |
+--------------------------+----------------------------+
| character_set_client     | latin1                     |
| character_set_connection | latin1                     |
| character_set_database   | latin1                     |
| character_set_filesystem | binary                     |
| character_set_results    | latin1                     |
| character_set_server     | latin1                     |
| character_set_system     | utf8                       |
| character_sets_dir       | /usr/share/mysql/charsets/ |
+--------------------------+----------------------------+
8 rows in set (0.001 sec)
Next:
``` MariaDB [magento]> SHOW VARIABLES LIKE '%coll%';```
MariaDB [magento]> SHOW VARIABLES LIKE '%coll%';
+----------------------+-------------------+
| Variable_name        | Value             |
+----------------------+-------------------+
| collation_connection | latin1_swedish_ci |
| collation_database   | latin1_swedish_ci |
| collation_server     | latin1_swedish_ci |
+----------------------+-------------------+

##### 1.3. Based on the output above you can build the `sed` commands to fix the issue:
`sed -i 's/collation_connection      = utf8mb4/collation_connection      = latin1_swedish_ci/g' store-2022-04-27.sql`
`sed -i 's/utf8mb4 COLLATE=utf8mb4_0900_ai_ci/latin1 COLLATE=latin1_swedish_ci/g store-2022-04-27.sql`

##### 1.4. Import the new file as usually with:
`cat store-2022-04-27.sql | warden db import` (or in other way specific to your environment)
