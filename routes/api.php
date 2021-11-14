<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::middleware('auth:api')->group(function () {

        Route::prefix('auth-private')->group(function () {
            Route::post('/register-questions', 'Api\v1\UserController@registerQuestions');
            Route::post('/change-password', 'Api\v1\UserController@change_password');
            Route::post('/edit-profile', 'Api\v1\UserController@edit_profile');
            Route::post('/settings', 'Api\v1\UserController@settings');
            Route::post('/change-location', 'Api\v1\UserController@change_location');
            Route::post('/add-payment-method', 'Api\v1\UserController@addPaymentMethod');
            Route::post('/change-payment-method', 'Api\v1\UserController@change_payment_method');
            Route::delete('/delete-payment-method', 'Api\v1\UserController@deletePaymentMethod');
            Route::get('/my-info', 'Api\v1\UserController@my_info');
            Route::get('/get-notification', 'Api\v1\UserController@get_notification');
            Route::post('/edit-profile-picture', 'Api\v1\UserController@edit_profile_picture');
            Route::get('/logout', 'Api\v1\UserController@logout');
            Route::post('/activate-account', 'Api\v1\UserController@activate_account');
            Route::get('/resend-verification-code', 'Api\v1\UserController@resend_verification_code');
            Route::get('/user-membership', 'Api\v1\UserController@userMembership');
            // Route::post('/save-fire-base-token', 'Api\v1\UserController@save_fire_base_token');
        });

        Route::prefix('general')->group(function () {
            Route::get('/home', 'Api\v1\GeneralController@home');
            Route::post('/tax-calculator', 'Api\v1\GeneralController@taxCalculator');
        });

        Route::prefix('savings')->group(function () {
            Route::post('/add-saving', 'Api\v1\SavingsController@addSaving');
            Route::post('/edit-saving/{savingId}', 'Api\v1\SavingsController@editSaving');
            Route::delete('/delete-saving/{savingId}', 'Api\v1\SavingsController@deleteSaving');
            Route::get('/all-savings', 'Api\v1\SavingsController@allSavings');
            Route::get('/single-saving/{savingId}', 'Api\v1\SavingsController@singleSaving');
            Route::post('/add-saving-record', 'Api\v1\SavingsController@addSavingRecord');
            Route::post('/edit-saving-record/{recordId}', 'Api\v1\SavingsController@editSavingRecord');
            Route::delete('/delete-saving-record/{recordId}', 'Api\v1\SavingsController@deleteSavingRecord');
            Route::get('/single-saving-with-log/{savingId}', 'Api\v1\SavingsController@singleSavingWithLog');
        });

        Route::prefix('liabilities')->group(function () {
            Route::post('/add-liability', 'Api\v1\LiabilitiesController@addLiability');
            Route::post('/edit-liability/{liabilityId}', 'Api\v1\LiabilitiesController@editLiability');
            Route::delete('/delete-liability/{liabilityId}', 'Api\v1\LiabilitiesController@deleteLiability');
            Route::get('/all-liabilities', 'Api\v1\LiabilitiesController@allLiabilities');
            Route::get('/single-liability/{liabilityId}', 'Api\v1\LiabilitiesController@singleLiability');
            Route::post('/add-liability-payment', 'Api\v1\LiabilitiesController@addLiabilityPayment');
            Route::post('/edit-liability-payment/{paymentId}', 'Api\v1\LiabilitiesController@editLiabilityPayment');
            Route::delete('/delete-liability-payment/{paymentId}', 'Api\v1\LiabilitiesController@deleteLiabilityPayment');
            Route::get('/single-liability-with-log/{liabilityId}', 'Api\v1\LiabilitiesController@singleLiabilityWithLog');
        });

        Route::prefix('revenues')->group(function () {
            Route::post('/add-revenue', 'Api\v1\RevenuesController@addRevenue');
            Route::post('/edit-revenue/{revenueId}', 'Api\v1\RevenuesController@editRevenue');
            Route::delete('/delete-revenue/{revenueId}', 'Api\v1\RevenuesController@deleteRevenue');
            Route::get('/all-revenues', 'Api\v1\RevenuesController@allRevenues');
            Route::get('/single-revenue/{revenueId}', 'Api\v1\RevenuesController@singleRevenue');
            Route::post('/add-revenue-record', 'Api\v1\RevenuesController@addRevenueRecord');
            Route::post('/edit-revenue-record/{recordId}', 'Api\v1\RevenuesController@editRevenueRecord');
            Route::delete('/delete-revenue-record/{recordId}', 'Api\v1\RevenuesController@deleteRevenueRecord');
            Route::get('/single-revenue-with-log/{revenueId}', 'Api\v1\RevenuesController@singleRevenueWithLog');
            Route::post('/add-to-revenue-from-savings', 'Api\v1\RevenuesController@addToRevenuefromSavings');
        });

        Route::prefix('expenses')->group(function () {
            Route::post('/add-expense', 'Api\v1\ExpensesController@addExpense');
            Route::post('/edit-expense/{expenseId}', 'Api\v1\ExpensesController@editExpense');
            Route::delete('/delete-expense/{expenseId}', 'Api\v1\ExpensesController@deleteExpense');
            Route::get('/all-basic-expenses', 'Api\v1\ExpensesController@allBasicExpenses');
            Route::get('/all-extras-expenses', 'Api\v1\ExpensesController@allExtrasExpenses');
            Route::get('/single-expense/{expenseId}', 'Api\v1\ExpensesController@singleExpense');
        });

        Route::prefix('shopping-list')->group(function () {
            Route::post('/add-shopping-list', 'Api\v1\ShoppingListController@addList');
            Route::post('/edit-shopping-list/{listId}', 'Api\v1\ShoppingListController@editList');
            Route::delete('/delete-shopping-list/{listId}', 'Api\v1\ShoppingListController@deleteList');
            Route::get('/single-shopping-list/{listId}', 'Api\v1\ShoppingListController@singleList');
            Route::get('/all-shopping-lists', 'Api\v1\ShoppingListController@allLists');
            Route::post('/add-shopping-list-item', 'Api\v1\ShoppingListController@addListItem');
            Route::post('/edit-shopping-list-item/{ItemId}', 'Api\v1\ShoppingListController@editListItem');
            Route::delete('/delete-shopping-list-item/{ItemId}', 'Api\v1\ShoppingListController@deleteListItem');
            Route::post('/change-list-status/{listId}', 'Api\v1\ShoppingListController@changeListStatus');
        });

        Route::prefix('goal')->group(function () {
            Route::post('/add-goal', 'Api\v1\GoalsController@addGoal');
            Route::post('/edit-goal/{GoalId}', 'Api\v1\GoalsController@editGoal');
            Route::delete('/delete-goal/{GoalId}', 'Api\v1\GoalsController@deleteGoal');
            Route::get('/all-active-goals', 'Api\v1\GoalsController@allActiveGoals');
            Route::get('/all-reached-goals', 'Api\v1\GoalsController@allReachedGoals');
            Route::get('/single-goal/{GoalId}', 'Api\v1\GoalsController@singleGoal');
            Route::post('/add-money-to-goal', 'Api\v1\GoalsController@addMoneyToGoal');
            Route::get('/set-goal-as-reached/{GoalId}', 'Api\v1\GoalsController@setGoalAsReached');
        });

        Route::prefix('general-info')->group(function () {
            Route::get('/how-tax-work', 'Api\v1\Dash\EducationalInfoController@allTaxEducationalInfo');
            Route::get('/about-finance', 'Api\v1\Dash\EducationalInfoController@allAboutFinanceEducationalInfo');
            Route::post('/download-file', 'Api\v1\Dash\EducationalInfoController@downloadFile');
        });

        Route::prefix('reports')->group(function () {
            Route::post('/revenues-export', 'Api\v1\ReportsController@revenuesExport');
            Route::post('/savings-export', 'Api\v1\ReportsController@savingsExport');
            Route::post('/liabilities-export', 'Api\v1\ReportsController@liabilitiesExport');
            Route::post('/expenses-export', 'Api\v1\ReportsController@expensesExport');
            Route::post('/shopping-lists-export', 'Api\v1\ReportsController@shoppingListsExport');
        });
    });

    Route::prefix('auth-general')->group(function () {
        Route::post('register','Api\v1\UserController@register');
        Route::post('login','Api\v1\UserController@login');
        Route::post('/forget-password', 'Api\v1\UserController@forget_password');
        Route::post('/reset-password', 'Api\v1\UserController@reset_password');
    });

    Route::prefix('general')->group(function () {
        Route::get('/problems', 'Api\v1\Dash\CommonProblemsController@allCommonProblems');
        Route::get('/quotas', 'Api\v1\Dash\QuotaController@allQuotas');
        Route::get('/time-periods', 'Api\v1\Dash\TimePeriodsController@alltimeperiods');
        Route::get('/jobs', 'Api\v1\Dash\JobsController@allJobs');
        Route::get('/sales-men', 'Api\v1\Dash\SalesController@allSales');
        Route::get('/contact-us', 'Api\v1\Dash\ContactUsController@showContactUs');
        Route::get('/all-about-us', 'Api\v1\Dash\AboutUsController@allAboutUs');
    });

    Route::post('/add-customer-support-request', 'Api\v1\Dash\CustomerSupportController@addCustomerSupportRequest');
});

Route::get('show-timePeriods','TestController@test');

