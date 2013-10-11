sample-php-code
===============

Sample Custom Framework that allows API and Portal access to data

The idea behind this framework is to allow the data to be accessed by either a front end portal or through Signed API requests and JSON returns

For example:
example.com will return a HTML interface
api.example.com will return a JSON array

this allows for easy access to the data, and easy use of AJAX in the portal

The main index includes the api config and framework files so everything runs off of the same codebase.

if the main index is called:
 - Session data will be used
 - then the response can either be HTML, or if it's an AJAX (xmlhttprequest) call can return JSON
 
the api index is called:
 - Must be a signed request
 - only JSON will be returned
 
 
Library Loading Structure:
--------------------------
 * Index
    * config
    * loader
        * utility
            * vendor/email
        * session
        * mysqlDao
            * classSQL
        * signed
        * router
            * ig_route
            * controller
                * view
                * model
                * validate

System Logic Overview:
----------------------
* IG routes will then load the selected app controller
* The app controller sets the models and views it needs to use
* once the logic for the controller is run the this->render() function should run.
* render() will push data back as JSON or load the template depending on the request type
* the template will load the view and small elements
* views can also load elements
