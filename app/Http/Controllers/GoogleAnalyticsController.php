<?php

namespace App\Http\Controllers;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\ApplicationDefaultCredentials;
use Illuminate\Http\Request;
use Throwable;

class GoogleAnalyticsController extends Controller
{
    private function guard(Request $request)
    {
        return $request->session()->has('crm_admin_id')
            ? null
            : redirect()->route('crm.login');
    }

    public function index(Request $request)
    {
        if ($response = $this->guard($request)) {
            return $response;
        }

        $measurementId = 'G-L52ZZL5MN0';
        $propertyId = '555962387';

        $dataApiConnected = false;
        $activeUsers = null;
        $newUsers = null;
        $sessions = null;
        $views = null;
        $dataApiError = null;

        $trafficSources = [];
        $landingPages = [];
        $countries = [];
        $devices = [];
        $dailyTrend = [];

        try {
            $credentialsPath = config('services.google_analytics.credentials');

            if ($credentialsPath) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
            }

            $credentials = ApplicationDefaultCredentials::getCredentials([
                'https://www.googleapis.com/auth/cloud-platform',
                'https://www.googleapis.com/auth/analytics.readonly',
            ]);

            $client = new BetaAnalyticsDataClient([
                'credentials' => $credentials,
            ]);

            $report = $client->runReport(
                (new RunReportRequest())
                    ->setProperty('properties/' . $propertyId)
                    ->setDateRanges([
                        (new DateRange())
                            ->setStartDate('7daysAgo')
                            ->setEndDate('today'),
                    ])
                    ->setMetrics([
                        (new Metric())->setName('activeUsers'),
                        (new Metric())->setName('newUsers'),
                        (new Metric())->setName('sessions'),
                        (new Metric())->setName('screenPageViews'),
                    ])
            );

            $dataApiConnected = true;

            foreach ($report->getRows() as $row) {
                $values = $row->getMetricValues();
                $activeUsers = (int) $values[0]->getValue();
                $newUsers = (int) $values[1]->getValue();
                $sessions = (int) $values[2]->getValue();
                $views = (int) $values[3]->getValue();
                break;
            }

            $advancedReports = [
                'trafficSources' => ['sessionSourceMedium', 'sessions', 10],
                'landingPages' => ['landingPagePlusQueryString', 'sessions', 10],
                'countries' => ['country', 'activeUsers', 10],
                'devices' => ['deviceCategory', 'activeUsers', 10],
                'dailyTrend' => ['date', 'activeUsers', 10],
            ];

            foreach ($advancedReports as $key => [$dimension, $metric, $limit]) {
                try {
                    $advancedReport = $client->runReport(
                        (new RunReportRequest())
                            ->setProperty('properties/' . $propertyId)
                            ->setDateRanges([
                                (new DateRange())
                                    ->setStartDate('7daysAgo')
                                    ->setEndDate('today'),
                            ])
                            ->setDimensions([
                                (new Dimension())->setName($dimension),
                            ])
                            ->setMetrics([
                                (new Metric())->setName($metric),
                                ...($key === 'dailyTrend'
                                    ? [(new Metric())->setName('sessions')]
                                    : []),
                            ])
                            ->setLimit($limit)
                    );

                    foreach ($advancedReport->getRows() as $row) {
                        $dimensionValue = $row->getDimensionValues()[0]->getValue();
                        $metricValues = $row->getMetricValues();

                        if ($key === 'dailyTrend') {
                            $dailyTrend[] = [
                                'date' => $dimensionValue,
                                'activeUsers' => (int) $metricValues[0]->getValue(),
                                'sessions' => (int) $metricValues[1]->getValue(),
                            ];
                        } else {
                            ${$key}[] = [
                                'label' => $dimensionValue,
                                'value' => (int) $metricValues[0]->getValue(),
                            ];
                        }
                    }
                } catch (Throwable $advancedException) {
                    report($advancedException);
                }
            }

            usort($dailyTrend, fn ($a, $b) => strcmp($a['date'], $b['date']));
        } catch (Throwable $e) {
            $dataApiError = 'Google Analytics reporting is temporarily unavailable.';
            report($e);
        }

        return view('crm.google-analytics', [
            'measurementId' => $measurementId,
            'dataApiConnected' => $dataApiConnected,
            'activeUsers' => $activeUsers,
            'newUsers' => $newUsers,
            'sessions' => $sessions,
            'views' => $views,
            'trafficSources' => $trafficSources,
            'landingPages' => $landingPages,
            'countries' => $countries,
            'devices' => $devices,
            'dailyTrend' => $dailyTrend,
            'dataApiError' => $dataApiError,
        ]);
    }
}
