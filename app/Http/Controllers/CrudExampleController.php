<?php

namespace App\Http\Controllers;

use App\Exports\CrudExampleExport;
use App\Http\Requests\CrudExampleRequest;
use App\Http\Requests\ImportExcelRequest;
use App\Imports\CrudExampleImport;
use App\Models\CrudExample;
use App\Repositories\CrudExampleRepository;
use App\Services\EmailService;
use App\Services\FileService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CrudExampleController extends Controller
{
    /**
     * crud example repository
     *
     * @var CrudExampleRepository
     */
    private CrudExampleRepository $crudExampleRepository;

    /**
     * file service
     *
     * @var FileService
     */
    private FileService $fileService;

    /**
     * email service
     *
     * @var FileService
     */
    private EmailService $emailService;

    /**
     * icon of module
     *
     * @var String
     */
    private String $icon = 'fa fa-atom';

    /**
     * constructor method
     *
     * @return void
     */
    public function __construct()
    {
        $this->crudExampleRepository = new CrudExampleRepository;
        $this->fileService           = new FileService;
        $this->emailService          = new EmailService;

        $this->middleware('can:Contoh CRUD');
        $this->middleware('can:Contoh CRUD Tambah')->only(['create', 'store']);
        $this->middleware('can:Contoh CRUD Ubah')->only(['edit', 'update']);
        $this->middleware('can:Contoh CRUD Detail')->only(['show']);
        $this->middleware('can:Contoh CRUD Hapus')->only(['destroy']);
        $this->middleware('can:Contoh CRUD Ekspor')->only(['json', 'excel', 'csv', 'pdf']);
        $this->middleware('can:Contoh CRUD Impor Excel')->only(['importExcel', 'importExcelExample']);
    }

    /**
     * showing crud example page
     *
     * @return Response
     */
    public function index()
    {
        $user = auth()->user();
        $isYajra = Route::is('yajra-crud-examples.index');
        if ($isYajra) {
            $data = collect([]);
        } else {
            $data = $this->crudExampleRepository->getLatest();
        }
        return view('stisla.crud-example.index', [
            'data'              => $data,
            'canCreate'         => $user->can('Contoh CRUD Tambah'),
            'canUpdate'         => $user->can('Contoh CRUD Ubah'),
            'canDetail'         => $user->can('Contoh CRUD Detail'),
            'canDelete'         => $user->can('Contoh CRUD Hapus'),
            'canImportExcel'    => $user->can('Contoh CRUD Impor Excel'),
            'canExport'         => $user->can('Contoh CRUD Ekspor'),
            'title'             => __('Contoh CRUD'),
            'moduleIcon'        => $this->icon,
            'route_create'      => route('crud-examples.create'),
            'routeImportExcel'  => route('crud-examples.import-excel'),
            'routeExampleExcel' => route('crud-examples.import-excel-example'),
            'routePdf'          => route('crud-examples.pdf'),
            'routeExcel'        => route('crud-examples.excel'),
            'routeCsv'          => route('crud-examples.csv'),
            'routeJson'         => route('crud-examples.json'),
            'isYajra'           => $isYajra,
        ]);
    }

    /**
     * datatable yajra index
     *
     * @return Response
     */
    public function yajraAjax()
    {
        return $this->crudExampleRepository->getYajraDataTables();
    }

    /**
     * showing add new crud example page
     *
     * @return Response
     */
    public function create()
    {
        $title      = __('Contoh CRUD');
        $routeIndex = route('crud-examples.index');
        $fullTitle  = __('Tambah Contoh CRUD');
        return view('stisla.crud-example.form', [
            'selectOptions'   => get_options(10),
            'radioOptions'    => get_options(4),
            'checkboxOptions' => get_options(5),
            'title'           => $title,
            'fullTitle'       => $fullTitle,
            'routeIndex'      => $routeIndex,
            'action'          => route('crud-examples.store'),
            'moduleIcon'      => $this->icon,
            'isDetail'        => false,
            'breadcrumbs'     => [
                [
                    'label' => __('Dashboard'),
                    'link'  => route('dashboard.index')
                ],
                [
                    'label' => $title,
                    'link'  => $routeIndex
                ],
                [
                    'label' => 'Tambah'
                ]
            ]
        ]);
    }

    /**
     * save new crud example to db
     *
     * @param CrudExampleRequest $request
     * @return Response
     */
    public function store(CrudExampleRequest $request)
    {
        $data = $request->only([
            'text',
            "number",
            "select",
            "textarea",
            "radio",
            "date",
            'checkbox',
            'checkbox2',
            "time",
            'tags',
            "color",
            'select2',
            'select2_multiple',
            'summernote',
            'summernote_simple',
        ]);
        if ($request->hasFile('file')) {
            $data['file'] = $this->fileService->uploadCrudExampleFile($request->file('file'));
        }
        $data['currency'] = str_replace(',', '', $request->currency);
        $data['currency_idr'] = str_replace('.', '', $request->currency_idr);
        $result = $this->crudExampleRepository->create($data);
        logCreate("Contoh CRUD", $result);
        $successMessage = successMessageCreate("Contoh CRUD");
        return back()->with('successMessage', $successMessage);
    }

    /**
     * get detail data
     *
     * @param CrudExample $crudExample
     * @param bool $isDetail
     * @return array
     */
    private function getDetail(CrudExample $crudExample, bool $isDetail = false)
    {
        $title       = __('Contoh CRUD');
        $routeIndex  = route('crud-examples.index');
        $breadcrumbs = [
            [
                'label' => __('Dashboard'),
                'link'  => route('dashboard.index')
            ],
            [
                'label' => $title,
                'link'  => $routeIndex
            ],
            [
                'label' => $isDetail ? 'Detail' : 'Ubah'
            ]
        ];
        return [
            'selectOptions'   => get_options(10),
            'radioOptions'    => get_options(4),
            'checkboxOptions' => get_options(5),
            'd'               => $crudExample,
            'title'           => $title,
            'fullTitle'       => $isDetail ? __('Detail Contoh CRUD') : __('Ubah Contoh CRUD'),
            'routeIndex'      => $routeIndex,
            'action'          => route('crud-examples.update', [$crudExample->id]),
            'moduleIcon'      => $this->icon,
            'isDetail'        => $isDetail,
            'breadcrumbs'     => $breadcrumbs,
        ];
    }

    /**
     * showing edit crud example page
     *
     * @param CrudExample $crudExample
     * @return Response
     */
    public function edit(CrudExample $crudExample)
    {
        $data = $this->getDetail($crudExample);
        return view('stisla.crud-example.form', $data);
    }

    /**
     * update data to db
     *
     * @param CrudExampleRequest $request
     * @param CrudExample $crudExample
     * @return Response
     */
    public function update(CrudExampleRequest $request, CrudExample $crudExample)
    {
        $data = $request->only([
            'text',
            "number",
            "select",
            "textarea",
            "radio",
            "date",
            'checkbox',
            'checkbox2',
            'tags',
            "time",
            "color",
            'select2',
            'select2_multiple',
            'summernote',
            'summernote_simple',
        ]);
        if ($request->hasFile('file')) {
            $data['file'] = $this->fileService->uploadCrudExampleFile($request->file('file'));
        }
        $data['currency'] = str_replace(',', '', $request->currency);
        $data['currency_idr'] = str_replace('.', '', $request->currency_idr);
        $newData = $this->crudExampleRepository->update($data, $crudExample->id);
        logUpdate("Contoh CRUD", $crudExample, $newData);
        $successMessage = successMessageUpdate("Contoh CRUD");
        return back()->with('successMessage', $successMessage);
    }

    public function show(CrudExample $crudExample)
    {
        $data = $this->getDetail($crudExample, true);
        return view('stisla.crud-example.form', $data);
    }

    /**
     * delete crud example from db
     *
     * @param CrudExample $crudExample
     * @return Response
     */
    public function destroy(CrudExample $crudExample)
    {
        $this->fileService->deleteCrudExampleFile($crudExample);
        $this->crudExampleRepository->delete($crudExample->id);
        logDelete("Contoh CRUD", $crudExample);
        $successMessage = successMessageDelete("Contoh CRUD");
        return back()->with('successMessage', $successMessage);
    }

    /**
     * download import example
     *
     * @return BinaryFileResponse
     */
    public function importExcelExample(): BinaryFileResponse
    {
        // bisa gunakan file excel langsung sebagai contoh
        // $filepath = public_path('example.xlsx');
        // return response()->download($filepath);

        $excel = new CrudExampleExport($this->crudExampleRepository->getLatest());
        return $this->fileService->downloadExcel($excel, 'crud_examples_import.xlsx');
    }

    /**
     * import excel file to db
     *
     * @param ImportExcelRequest $request
     * @return Response
     */
    public function importExcel(ImportExcelRequest $request)
    {
        $this->fileService->importExcel(new CrudExampleImport, $request->file('import_file'));
        $successMessage = successMessageImportExcel("Contoh CRUD");
        return back()->with('successMessage', $successMessage);
    }

    /**
     * download export data as json
     *
     * @return Response
     */
    public function json()
    {
        $data = $this->crudExampleRepository->getLatest();
        return $this->fileService->downloadJson($data, 'crud_examples.json');
    }

    /**
     * download export data as xlsx
     *
     * @return Response
     */
    public function excel()
    {
        $data  = $this->crudExampleRepository->getLatest();
        $excel = new CrudExampleExport($data);
        return $this->fileService->downloadExcel($excel, 'crud_examples.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * download export data as csv
     *
     * @return Response
     */
    public function csv()
    {
        $data  = $this->crudExampleRepository->getLatest();
        $excel = new CrudExampleExport($data);
        return $this->fileService->downloadExcel($excel, 'crud_examples.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * download export data as pdf
     *
     * @return Response
     */
    public function pdf(): Response
    {
        $data = [
            'data'     => $this->crudExampleRepository->getLatest(),
            'isExport' => true
        ];
        // return $this->fileService->downloadPdfLetter('stisla.crud-example.export-pdf', $data, 'crud_examples.pdf');
        // return $this->fileService->downloadPdfLegal('stisla.crud-example.export-pdf', $data, 'crud_examples.pdf');
        return $this->fileService->downloadPdfA2('stisla.crud-example.export-pdf', $data, 'crud_examples.pdf');
    }
}
