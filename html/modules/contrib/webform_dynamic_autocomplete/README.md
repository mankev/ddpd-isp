This module provides a new element field in webform for Dynamically handling Autocomplete API request. In this, you can define your own API end point in the configuration. As per the defined endpoint, you will get the autocomplete options. Presently you have to select the autocomplete options from the given options in Autocomplete field in webform.

The Api endpoint must return the data in form of key and value pairs, only then the autocomplete dynamic field will work properly. otherwise It will not work as required.

There is Configuration Settings form at below url:-

/admin/config/webform_dynamic_autocomplete/settings

Here , you have 2 fields

1. Endpoint URL--It should be an get request api endpoint with json response in key-value form
   ex:-https://www.drupal.org/publicdata
   2.Query Parameter--- which will take your searched value in the autocomplete field.
   example:- "a"

the complete Url should be like below dummy example:-

https://www.drupal.org/publicdata?a="searched-parameter"

Note:- Without above configuration your module will not work.

3.After above configuration go to your webform and add an autocomplete element

4. Go to Select Custom Options field. IN this field you will find your Dynamic Autocomplete Option.
5. Choose the Option and save the element.
6. Go to your form page and check the Autocomplete Field. It should give you the data as required in autocomplete form for your external or dynamic Api.
