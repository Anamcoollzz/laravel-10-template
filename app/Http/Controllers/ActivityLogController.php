<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogExport;
use App\Repositories\ActivityLogRepository;
use App\Repositories\UserRepository;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\Facade as PDF;

class ActivityLogController extends Controller
{
    /**
     * activityLogRepository
     *
     * @var ActivityLogRepository
     */
    private ActivityLogRepository $activityLogRepository;

    /**
     * userRepository
     *
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * file service
     *
     * @var FileService
     */
    private FileService $fileService;

    /**
     * constructor method
     *
     * @return void
     */
    public function __construct()
    {
        $this->activityLogRepository = new ActivityLogRepository;
        $this->userRepository        = new UserRepository;
        $this->fileService           = new FileService;

        $this->middleware('can:Log Aktivitas');
    }

    /**
     * showing page data
     *
     * @return Response
     */
    public function index()
    {
        $user            = auth()->user();
        $user            = $this->userRepository->find($user->id);
        $queryString     = request()->query();
        $data            = $this->activityLogRepository->getFilter();
        $roles           = $this->userRepository->getRoleOptions();
        $users           = $this->userRepository->getUserOptions();
        $kinds           = $this->activityLogRepository->getActivityTypeOptions();
        $browserOptions  = $this->activityLogRepository->getBrowserOptions();
        $platformOptions = $this->activityLogRepository->getPlatformOptions();
        $deviceOptions   = $this->activityLogRepository->getDeviceOptions();

        return view('stisla.activity-logs.index', [
            'data'             => $data,
            'users'            => $users,
            'roles'            => $roles,
            'kinds'            => $kinds,
            'browserOptions'   => $browserOptions,
            'platformOptions'  => $platformOptions,
            'deviceOptions'    => $deviceOptions,
            'canCreate'        => false,
            // 'canCreate'        => $user->can('Log Aktivitas Tambah'),
            // 'canUpdate'        => $user->can('Log Aktivitas Ubah'),
            // 'canDelete'        => $user->can('Log Aktivitas Hapus'),
            // 'canImportExcel'   => $user->can('Log Aktivitas Impor Excel'),
            'canExport'        => $user->can('Log Aktivitas Ekspor'),
            'title'            => __('Log Aktivitas'),
            'routeCreate'      => null,
            // 'routeCreate'      => route('activity-logs.create'),
            'routePdf'         => route('activity-logs.pdf', $queryString),
            'routePrint'       => route('activity-logs.print', $queryString),
            'routeExcel'       => route('activity-logs.excel', $queryString),
            'routeCsv'         => route('activity-logs.csv', $queryString),
            'routeJson'        => route('activity-logs.json', $queryString),
            'isSuperAdmin'     => $user->hasRole('superadmin'),
            // 'routeImportExcel' => route('activity-logs.import-excel'),
            // 'excelExampleLink' => route('activity-logs.import-excel-example'),
        ]);
    }

    /**
     * download export data as json
     *
     * @return Response
     */
    public function json()
    {
        $data = $this->activityLogRepository->getFilter();
        return $this->fileService->downloadJson($data, 'activity-logs.json');
    }

    /**
     * download export data as xlsx
     *
     * @return Response
     */
    public function excel()
    {
        $data = $this->activityLogRepository->getFilter();
        return (new ActivityLogExport($data))->download('activity-logs.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * download export data as csv
     *
     * @return Response
     */
    public function csv()
    {
        $data = $this->activityLogRepository->getFilter();
        return (new ActivityLogExport($data))->download('activity-logs.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * download export data as pdf
     *
     * @return Response
     */
    public function pdf()
    {
        $data = $this->activityLogRepository->getFilter();
        return PDF::setPaper('Letter', 'landscape')
            ->loadView('stisla.activity-logs.export-pdf', [
                'data'    => $data,
                'isPrint' => false
            ])
            ->download('activity-logs.pdf');
    }

    /**
     * export data to print html
     *
     * @return Response
     */
    public function exportPrint()
    {
        $data = $this->activityLogRepository->getFilter();
        return view('stisla.activity-logs.export-pdf', [
            'data'    => $data,
            'isPrint' => true
        ]);
    }
}
