# East Boston Data

East Boston Data views from data.boston.gov

Basically these just use the SQL endpoints to view recent data.

## Notes

## General

* Assessing `CM_ID` is "Condo Main"

## Tips for SQL via CKAN DataStore extension

* The docs for `SELECT` at http://docs.ckan.org/en/latest/maintaining/datastore.html?highlight=sql#ckanext.datastore.logic.action.datastore_search_sql are light.
* The SQL validator is **very** strict, so you can't miss a quote
* **409 Conflict** indicates a SQL validation error (e.g. PostgreSQL uses only single quotes for values (i.e. `WHERE "name" = 'John'`). Double quotes are used to quote system identifiers; field names, table names, etc. (i.e. `WHERE "last name" = 'Smith'`).
* **500 Internal Server** iddicates an issue executive the SQL (e.g. `''::double` casting empty string to a double doesn't work.)

### masslandrecords.com/Suffolk Crawling

* Three parameters seem to matter: 1) `ScriptManager1` 2) `__EVENTTARGET` and 3) a value within the cookie (seems to be `TS6d86e7`)
* This one `DocList1$UpdatePanel|DocList1$GridView_Document$ctl02$ButtonRow_Doc. #_0` seems predictable; you just have to increment the numbers
* The cookie seems to refer to some state on the server, which changes on some events (like pagination.)  Perhaps a workaround is setting 100/page


