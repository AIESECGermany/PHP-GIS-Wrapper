# PHP GIS Wrapper
The PHP GIS Wrapper is a PHP library to connect your PHP projects with AIESEC's Global Information System.

It gives you the possibility to access every resource in the GIS as if it would be a native object in your php script. You don't need to take care about any requests, parsing or the token generation. Just instantiate an Authentication Provider with a user name and password. Then instantiate the GIS Wrapper and you are ready to go.

Version 0.3 is a partial rewrite with breaking changes in the acccess of endpoints, please check the documentation and test your application, if you update.

If you already used the PHP GIS Wrapper v0.1 please be aware that v0.2 is a complete rewrite. There are a lot of architectural changes whereby the update of your projects is most probably not that simple. The new version definitely gives you a performance boost and brings many new possibilities. Please check the changelog for further informations.

- author: Lukas Ehnle <lukas.ehnle@aiesec.de>
- author until v0.2: Karl Johann Schubert <karljohann@familieschubi.de>
- version: 0.3

# Documentation

## Installation
1. install composer (https://getcomposer.org/)
2. `composer require aiesecgermany/php-gis-wrapper`
3. require the composer autoloader in your scripts

## AuthProviders
The file `AuthProvider.php` provides an interface for Authentication Providers. The Purpose of an Authentication Provider is to provide an access token to access the GIS API.

At the moment there are three main Authentication Providers:
* `AuthProviderEXPA($username, $password)` to get an access token like you would login to EXPA.
* `AuthProviderOP($username, $password)` to get an access token like you would login to OP.
* `AuthProviderCombined($username, $password)` this provider tries to get an EXPA token and if it is invalid returns an OP token.

Furthermore there are two special Authentication Providers
* `AuthProviderShadow($tokan, $authProvider)` which takes a valid token as first argument and another AuthProvider as second argument. You may use this AuthProvider when you cache tokens.
* `AuthProviderNationalIdentity($url)` which can be used with the customTokenFlow of the [AIESEC Identity](https://github.com/AIESEC-Egypt/aiesec-identity) Project.

<i>Hint: If you want to synchronise your national or local systems with the GIS, just create a new account and a new team in your office. Then match the new account as team leader in the new team. Now you can use the credentials of this account and generate access tokens for your sync.</i>

Every Authentication Provider has to provide the function `getToken()` and `getNewToken()` the second function is used by the API wrapper if the API responds with an error, that the access token expired, to try it with a new token. That is useful, when the Authentication Provider caches the access token and has no option to determine if it's still valid.

### How to choose the right main Auth Provider
* if you have a predefined and active user: AuthProviderEXPA
* if you only want to authenticate active users: AuthProviderEXPA (Remember: If you get an access token this does not mean that the user is active, so if you need to know that use the current_person endpoint to validate the token)
* if you authenticate both active and non-active users: AuthProviderCombined
* if you only need OP rights, or have only non-active users: AuthProviderOP

Try to use AuthProviderEXPA and AuthProviderOP as much as possible. The AuthProviderCombined directly gives you the current person object and thereby validates if the token is an EXPA or OP token, but therefore he needs some more requests.

Especially if you want to authenticate only active users, use AuthProviderEXPA and validate the token afterwards. The AuthProviderCombined would make a request more, to generate an OP token.

### Keep the GIS Identity session
When a user access one of the frontends of the GIS he is redirected to the GIS Identity Service at auth.aiesec.org. This service opens a session for the user, whereby he do not need to login twice when he access another frontend. By now all three main Authentication Providers can make use of this session. On the one hand this can improve the performance of your script. On the other hand you can also generate an access token without saving the user credentials, just by keeping the session file.

You can set the filepath of the session via the function `setSession($path)`. The function `getSession()` returns the current session path. The session file must not exist beforehand, but the directory and the file must be writeable for PHP.

If you want to generate an access token from an existing session without having the user credentials, instantiate on of the standard AuthProviders with the filepath to the session as first parameter and leave the second parameter empty or set it to null. When the session file does not exist this will produce a E_USER_ERROR php error. If the session is invalid the generation of a token will throw a InvalidCredentials Exception.

Please make sure to call the function `setSession($path)` before you generate any access token. Everything else will work, but could lead to a inconsistent behaviour.

### helper functions
- All three main Authentication Providers support a boolean as third argument for the constructor. Setting this argument to false will disable the SSL Peer Verification. Set the second argument to `null` if you instantiate the AuthProvider with a session
- All three main Authentication Providers provide the function `getExpiresAt()`, which returns the timestamp until when the current access token is valid.
- The `AuthProviderCombined` furthermore provides:
    - the functions `isOP()` and `isEXPA()` which return a bool depending on the scope of the token
    - the function `getType()` which returns 'EXPA' or 'OP' depending on the scope of the token
    - the function `getCurrentPerson()` which returns the current person object, because it have to load this to validate the token
- The `AuthProviderShadow` provides the function `getAuthProvider()`, which returns the underlaying AuthProvider or null

## Class GIS
The class GIS is the entry point to access AIESECs Global Information System from your project. The first argument must be an AuthProvider. The second parameter can either be empty.

For simple projects it is fine to leave the second argument empty.
```
$user = new \GISwrapper\AuthProviderEXPA($username, $password);
$gis = new \GISwrapper\GIS($user);
```

### Caching
This new version does not need or support caching. As it does not parse a swagger file anymore.

## Data Access and Manipulation
Please check the api documentation at http://apidocs.aies.ec/ to get to know which endpoints exists. (<b>Attention:</b> make sure to change the file to the docs.json from v1 to v2)

Starting from your instance of the GIS (e.g. $gis) every part after /v2/ of the path is turned into an object.
```
// /v2/opportunities.json
$gis->opportunities;

// /v2/opportunities/{opportunity_id}.json
$gis->opportunities->{opportunity_id}

// /v2/opportunities/{opportunity_id}/progress.json
$gis->opportunities->{opportunity_id}->progress
```

### Getting data
There are two different kinds of endpoints. Those who return just one resource like /v2/current_person.json and those who return different pages each with a list of resources.

To get data from the fist kind, just call the get method.
```
// /v2/current_person.json
$res = $gis->current_person->get();
print_r($res);
```

The second kind of endpoint is accessable via an Iterator, so most probalby you want to use an foreach loop.
```
// /v2/opportunities.json
foreach($gis->opportunities as $o) {
    print_r($o);
}
```

### Create a resource
Please check the paragraph Parameters to get to know how to access the parameters of an endpoint. After you set all parameters which are necessary to create a new object call the `post()` function on that endpoint.

Please check the examples folder for an script on how to create, update and delete a new opportunity.

Endpoints who support the creation of a new object are those who support the http method POST. Please check the respective endpoint documentation for the required parameters.

### Update an existing resource
After setting the necessary parameters on the endpoint you want to update, call the `patch()` method on that endpoint.

Please check the examples folder for an script on how to create, update and delete a new opportunity.

Endpoints who support updates, are those which support the http method PATCH. Please check the respective endpoint documentation for the required parameters.

### Delete a resource
To delete an resource call the `delete()` method on that endpoint.

Endpoint who support the delete methode are those which support the http method DELETE. Please check the api documentation to find those endpoints.

## Parameters
Every Endpoint on the GIS API has parameters. Some parameters are already part of the path. Like already described those parameters turn into objects.

The GIS wrapper already takes care of the parameters access_token, page and per_page. Thereby you can not access or change them.

All other parameters of the parameter type query and form turn into an associative array of the endpoint.

Let's take a look at the endpoint `/v2/opportunities.json`
```
$gis->opportunities[q] = "some String"; // set parameter q
$gis->opportunities->[filters] = [
    "organisation" => 10, // set parameter filters[organisation]
    "issues" => [10, 20], // set elements of the array parameter filters[issues]
    "skills" => [ // set the ids of elements of the array parameter filters[skills]
        ["id" => 10],
        ["id" => 20]
    ]
] 
```

### setting many parameters at once
When you want to set many parameters at once without using the long notation with subobjects you can set them as array. Please be aware that this method can be hard to debug.

When you assign an Array to an Endpoint or Parameter the value of each key will be assigned recursively to the sub endpoints and parameter named like the key. This does not work when you have a dynamic part of the path in your array, but as soon as you assign the equivalent array to the last dynamic endpoint it will work.

The example from above would look like below
```
$gis->opportunities = [
    "q" => "some String",
    "filters" => [
        "issues" => [10, 20],
        "skills" => [
            ["id" => 10],
            ["id" => 20]
        ]
    ]
]
```

# Changelog
## 0.3.0
- rewrite that does not parse the swagger file, instead everything is allowed as an endpoint, so you have to take care to call the correct endpoints.

## 0.2.5
- added AuthProviderShadow and AuthProviderNationalIdentity
- added ServiceProvider for Lumen (should also work with Laravel)
- added `currentPage()`, `setStartPage($page)` and `setPerPage($number)` function to paged endpoints (tests still missing)
- updated Unit Tests

## 0.2.4
- Fixed some minor bugs in the API Endpoint

## 0.2.3
- Fixed the token regeneration when a GET request runs with an expired token

## 0.2.2
- Fixed an issue in the parameter validation, which occured when parameters of different methods had the same name

## 0.2.1
- improved stability of the swagger parsing

## 0.2
In this version the PHP-GIS-Wrapper was completely refactored. The most important changes are:
- New system architecture, especially for the swagger parser. This leads to cleaner source code and a big performance boost.
- Ability to cache the swagger parsing result, which provides even better performance, especially for big projects
- support the GIS Identity session in all three Authentication Providers, which can improve performance and gives you another set of opportunities
- ArrayAccess for dynamic path parts makes the usage far more intuitive
- Far better support for Array Parameters
- Validation of Parameter types
- the PHP GIS Wrapper became a Composer Package

## 0.1
This was the initial version of the PHP-GIS-Wrapper. It only supported GET requests.
- Originally there was only one AuthProvider called AuthProviderUser
- With the introduction of the GIS v2 this Provider was updated to the new GIS Identity, but then only supported EXPA users
- Later the AuthProviderUser was replaced by AuthProviderEXPA, AuthProviderOP and AuthProviderCombined

# FAQ

If you found a bug please open an issue in the github repository.

If you wrote another example just send a pull request

