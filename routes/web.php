<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
     $router->get('test',  ['uses' => 'LoginController@test']);
  
 
  
    $router->post('login', ['uses' => 'LoginController@login']);
  
 /*    $router->delete('authors/{id}', ['uses' => 'AuthorController@delete']);
  
    $router->put('authors/{id}', ['uses' => 'AuthorController@update']); */
  });

  $router->group(['prefix' => 'superAdmin'], function () use ($router) {
   
    $router->get('getUsers',  ['uses' => 'SuperAdminController@getUsers']);
    $router->post('createAdmin',  ['uses' => 'SuperAdminController@createUser']);
    $router->put('editAdmin',  ['uses' => 'SuperAdminController@editUser']);
    $router->get('allAdmin',  ['uses' => 'SuperAdminController@allAdmin']);
    $router->put('disableAdmin/{id}',  ['uses' => 'SuperAdminController@disableAdmin']);
    $router->put('enableAdmin/{id}',  ['uses' => 'SuperAdminController@enableAdmin']);
    $router->post('changePassword',  ['uses' => 'LoginController@changePassword']);

    
   // $router->post('sendMail',  ['uses' => 'SuperAdminController@sendMail']);
    
 
/*    $router->get('users', ['uses' => 'LoginController@getUsers']); 
 
   $router->post('login', ['uses' => 'LoginController@login']);
  */
/*    $router->delete('authors/{id}', ['uses' => 'AuthorController@delete']);
 
   $router->put('authors/{id}', ['uses' => 'AuthorController@update']); */
 });


 $router->group(['prefix' => 'admin'], function () use ($router) {
   
  $router->get('allZones',  ['uses' => 'AdminController@getAllZones']);
  $router->post('createZone',  ['uses' => 'AdminController@createZone']);
  $router->post('createZonalCoordinator',  ['uses' => 'AdminController@createZonalCoordinator']);
  $router->get('zonalCoordinator',  ['uses' => 'AdminController@viewZonalCoordinator']);
  $router->get('zonalCoordinatorInfo/{id}',  ['uses' => 'AdminController@zonalCoordinatorInfo']);
  $router->put('changeZonalCoordinator',  ['uses' => 'AdminController@changeZonalCoordinator']);
  $router->post('createCollege',  ['uses' => 'AdminController@createCollege']);
  $router->get('allCollege',  ['uses' => 'AdminController@allCollege']);
  $router->put('updateCollege',  ['uses' => 'AdminController@updateCollege']);
  $router->get('resetCollegePassword/{id}',  ['uses' => 'AdminController@resetCollegePassword']);
  $router->post('createGame',  ['uses' => 'AdminController@createGame']);
  $router->put('editGame',  ['uses' => 'AdminController@editGame']);
  $router->get('allGames',  ['uses' => 'AdminController@allGames']);
  $router->get('basicInformation',  ['uses' => 'AdminController@basicInformation']);
  $router->put('updateBasicInfo',  ['uses' => 'AdminController@updateBasicInformation']);
  $router->post('changePassword',  ['uses' => 'LoginController@changePassword']);
  


});

$router->group(['prefix' => 'zonalCoordinator'], function () use ($router) {
   
  $router->get('allColleges',  ['uses' => 'ZonalCoordinatorController@allColleges']);
  $router->get('verify/{id}',  ['uses' => 'ZonalCoordinatorController@sendEligiblilityPerforma']);
  $router->get('zoneInfo',  ['uses' => 'ZonalCoordinatorController@findZoneNoName']);
  $router->get('collegeInfrastructure/{id}',  ['uses' => 'ZonalCoordinatorController@collegeInfrastructre']);
  
  $router->post('createEvent',  ['uses' => 'ZonalCoordinatorController@createEvent']);
  $router->get('games',  ['uses' => 'ZonalCoordinatorController@gameData']);
  $router->get('events',  ['uses' => 'ZonalCoordinatorController@allEvents']);
  $router->get('eventRegistrationDetails',  ['uses' => 'ZonalCoordinatorController@eventRegistrationDetails']);
  $router->get('eventColleges/{id}',  ['uses' => 'ZonalCoordinatorController@eventColleges']);
  $router->get('currentEvents',  ['uses' => 'ZonalCoordinatorController@todayEvents']);
  $router->post('publishResult',  ['uses' => 'ZonalCoordinatorController@publishResult']);
  $router->get('viewResults',  ['uses' => 'ZonalCoordinatorController@viewResults']);
  $router->post('addCouncilMembers',  ['uses' => 'ZonalCoordinatorController@addCouncilMembers']);
  $router->put('editCouncilMembers',  ['uses' => 'ZonalCoordinatorController@editCouncilMembers']);
  $router->get('councilMembers',  ['uses' => 'ZonalCoordinatorController@councilMembers']);


});

$router->group(['prefix' => 'college'], function () use ($router) {
   
   $router->get('events',  ['uses' => 'CollegeController@allEvents']);
   $router->get('info',  ['uses' => 'CollegeController@collegeInfo']);

   //eligibility Performa
   $router->get('eligibilityPerforma/{id}',  ['uses' => 'CollegeController@sendEligiblilityPerforma']);
   $router->post('eligibilityPerforma',  ['uses' => 'CollegeController@EligibilityPerforma']);

   $router->get('gameCollege/{id}',  ['uses' => 'CollegeController@EligibilityPerformaEventGameDetails']);

   $router->get('eventEnrollment/{id}',  ['uses' => 'CollegeController@eventGameDetails']);
   $router->get('eventStudent/{id}',  ['uses' => 'CollegeController@eventStudentDetails']);

   $router->get('viewInfrastructure',  ['uses' => 'CollegeController@infrastructure']);
   $router->post('infrastructureUpload',  ['uses' => 'CollegeController@infrastructureUpload']);

   $router->post('changePassword',  ['uses' => 'LoginController@changePassword']);
   $router->put('updateBasicInfo',  ['uses' => 'CollegeController@updateBasicInformation']);



   $router->post('file',  ['uses' => 'CollegeController@file_upload_test']);
   $router->get('image',  ['uses' => 'CollegeController@returnImage']);


   $router->get('qrcode/{id}',  ['uses' => 'CollegeController@generateQrCode']);



   $router->post('formQr',  ['uses' => 'CollegeController@formQr']);

    $router->get('test',  ['uses' => 'CollegeController@showDateTime']); 


});