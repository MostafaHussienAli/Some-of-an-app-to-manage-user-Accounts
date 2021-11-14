<?php

use Illuminate\Http\Request;

/*
* Dashboard APIs [Admins , Comon problems , Sales, Copouns]
*/

Route::prefix('v1')->group(function () {
    Route::middleware('auth:api-dash')->group(function () {

        Route::prefix('admin')->group(function () {
            Route::post('/add-admin', 'Api\v1\Dash\AdminsController@addAdmin');
            Route::post('/edit-admin/{AdminId}', 'Api\v1\Dash\AdminsController@editAdmin');
            Route::delete('/delete-admin/{AdminId}', 'Api\v1\Dash\AdminsController@deleteAdmin');
            Route::get('/all-admins', 'Api\v1\Dash\AdminsController@allAdmins');
            Route::get('/single-admin/{AdminId}', 'Api\v1\Dash\AdminsController@singleAdmin');
        });

        Route::prefix('common-problem')->group(function () {
            Route::post('/add-common-problem', 'Api\v1\Dash\CommonProblemsController@addCommonProblem');
            Route::post('/edit-common-problem/{CommonProblemId}', 'Api\v1\Dash\CommonProblemsController@editCommonProblem');
            Route::delete('/delete-common-problem/{CommonProblemId}', 'Api\v1\Dash\CommonProblemsController@deleteCommonProblem');
            Route::get('/all-common-problems', 'Api\v1\Dash\CommonProblemsController@allCommonProblems');
            Route::get('/single-common-problem/{CommonProblemId}', 'Api\v1\Dash\CommonProblemsController@singleCommonProblem');
        });

        Route::prefix('customer-support')->group(function () {
            Route::delete('/delete-customer-support-request/{CustomerSupportRequestId}', 'Api\v1\Dash\CustomerSupportController@deleteCustomerSupportRequest');
            Route::get('/all-customer-support-requests', 'Api\v1\Dash\CustomerSupportController@allCustomerSupportRequests');
            Route::get('/single-customer-support-request/{CustomerSupportRequestId}', 'Api\v1\Dash\CustomerSupportController@singleCustomerSupportRequest');
        });

        Route::prefix('quota')->group(function () {
            Route::post('/add-quota', 'Api\v1\Dash\QuotaController@addQuota');
            Route::post('/edit-quota/{QuotaId}', 'Api\v1\Dash\QuotaController@editQuota');
            Route::delete('/delete-quota/{QuotaId}', 'Api\v1\Dash\QuotaController@deleteQuota');
            Route::get('/all-quotas', 'Api\v1\Dash\QuotaController@allQuotas');
            Route::get('/single-quota/{QuotaId}', 'Api\v1\Dash\QuotaController@singleQuota');
        });

        Route::prefix('time-period')->group(function () {
            Route::post('/add-time-period', 'Api\v1\Dash\TimePeriodsController@addTimePeriod');
            Route::post('/edit-time-period/{timePeriodId}', 'Api\v1\Dash\TimePeriodsController@editTimePeriod');
            Route::delete('/delete-time-period/{timePeriodId}', 'Api\v1\Dash\TimePeriodsController@deleteTimePeriod');
            Route::get('/all-time-periods', 'Api\v1\Dash\TimePeriodsController@alltimeperiods');
            Route::get('/single-time-period/{timePeriodId}', 'Api\v1\Dash\TimePeriodsController@singleTimePeriod');
        });

        Route::prefix('job')->group(function () {
            Route::post('/add-job', 'Api\v1\Dash\JobsController@addJob');
            Route::post('/edit-job/{JobId}', 'Api\v1\Dash\JobsController@editJob');
            Route::delete('/delete-job/{JobId}', 'Api\v1\Dash\JobsController@deleteJob');
            Route::get('/all-jobs', 'Api\v1\Dash\JobsController@allJobs');
            Route::get('/single-job/{JobId}', 'Api\v1\Dash\JobsController@singleJob');
        });

        Route::prefix('educational-info')->group(function () {
            Route::post('/add-type', 'Api\v1\Dash\EducationalInfoController@addType');
            Route::post('/edit-type/{TypeId}', 'Api\v1\Dash\EducationalInfoController@editType');
            Route::delete('/delete-type/{TypeId}', 'Api\v1\Dash\EducationalInfoController@deleteType');
            Route::get('/all-types', 'Api\v1\Dash\EducationalInfoController@allTypes');
            Route::get('/single-type/{TypeId}', 'Api\v1\Dash\EducationalInfoController@singleType');

            Route::post('/add-educational-section', 'Api\v1\Dash\EducationalInfoController@addEducationalSection');
            Route::post('/edit-educational-section/{EducationalSectionId}', 'Api\v1\Dash\EducationalInfoController@editEducationalSection');
            Route::delete('/delete-educational-section/{EducationalSectionId}', 'Api\v1\Dash\EducationalInfoController@deleteEducationalSection');
            Route::get('/all-educational-sections', 'Api\v1\Dash\EducationalInfoController@allEducationalSections');
            Route::get('/single-educational-section/{EducationalSectionId}', 'Api\v1\Dash\EducationalInfoController@singleEducationalSection');

            Route::post('/add-educational-info', 'Api\v1\Dash\EducationalInfoController@addEducationalInfo');
            Route::post('/edit-educational-info/{EducationalInfoId}', 'Api\v1\Dash\EducationalInfoController@editEducationalInfo');
            Route::delete('/delete-educational-info/{EducationalInfoId}', 'Api\v1\Dash\EducationalInfoController@deleteEducationalInfo');
            Route::get('/all-educational-info', 'Api\v1\Dash\EducationalInfoController@allEducationalInfo');
            Route::get('/single-educational-info/{EducationalInfoId}', 'Api\v1\Dash\EducationalInfoController@singleEducationalInfo');
        });

        Route::prefix('contact-us')->group(function () {
            Route::post('/add-contact-us', 'Api\v1\Dash\ContactUsController@addContactUs');
            Route::post('/edit-contact-us', 'Api\v1\Dash\ContactUsController@editContactUs');
            Route::get('/show-contact-us', 'Api\v1\Dash\ContactUsController@showContactUs');
            Route::get('/contact-us-for-edit', 'Api\v1\Dash\ContactUsController@showContactUsForEdit');
        });

        Route::prefix('about-us')->group(function () {
            Route::post('/add-about-us', 'Api\v1\Dash\AboutUsController@addAboutUs');
            Route::post('/edit-about-us/{AboutUsId}', 'Api\v1\Dash\AboutUsController@editAboutUs');
            Route::delete('/delete-about-us/{AboutUsId}', 'Api\v1\Dash\AboutUsController@deleteAboutUs');
            Route::get('/single-about-us/{AboutUsId}', 'Api\v1\Dash\AboutUsController@singleAboutUs');
            Route::get('/all-about-us', 'Api\v1\Dash\AboutUsController@allAboutUs');
        });

        Route::prefix('users')->group(function () {
            Route::get('/show-users', 'Api\v1\UserController@allUsers');
            Route::get('/show-single-user/{userId}', 'Api\v1\UserController@singleUser');
        });

        Route::prefix('promo-code')->group(function () {
            Route::post('/add-promo-code', 'Api\v1\Dash\PromoCodeController@addPromoCode');
            Route::post('/edit-promo-code/{PromoCodeId}', 'Api\v1\Dash\PromoCodeController@editPromoCode');
            Route::delete('/delete-promo-code/{PromoCodeId}', 'Api\v1\Dash\PromoCodeController@deletePromoCode');
            Route::get('/single-promo-code/{PromoCodeId}', 'Api\v1\Dash\PromoCodeController@singlePromoCode');
            Route::get('/all-promo-codes', 'Api\v1\Dash\PromoCodeController@allPromoCodes');
        });

        Route::prefix('sales')->group(function () {
            Route::post('/add-sales', 'Api\v1\Dash\SalesController@addSales');
            Route::post('/edit-sales/{SalesId}', 'Api\v1\Dash\SalesController@editSales');
            Route::delete('/delete-sales/{SalesId}', 'Api\v1\Dash\SalesController@deleteSales');
            Route::get('/single-sales/{SalesId}', 'Api\v1\Dash\SalesController@singleSales');
            Route::get('/all-sales', 'Api\v1\Dash\SalesController@allSales');
            Route::get('/sales-commission/{SalesId}', 'Api\v1\Dash\SalesController@salesCommission');
            Route::get('/pay-commission/{SalesId}', 'Api\v1\Dash\SalesController@payCommission');
        });

    });

    Route::post('/login', 'Api\v1\Dash\LoginController@login');

});
