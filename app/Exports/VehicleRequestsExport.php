<?php

namespace App\Exports;

use App\Models\VehicleRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PHPExcel;
use PHPExcel_IOFactory;

class VehicleRequestsExport
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Export ke Excel
     * 
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        // Buat objek PHPExcel
        $excel = new PHPExcel();
        
        // Set properti dokumen
        $excel->getProperties()->setCreator('Fleet Management System')
            ->setLastModifiedBy('Fleet Management System')
            ->setTitle('Laporan Pemesanan Kendaraan')
            ->setSubject('Laporan Pemesanan Kendaraan')
            ->setDescription('Laporan Pemesanan Kendaraan');
            
        // Set worksheet aktif
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Laporan Pemesanan');
        
        // Set judul kolom
        $headers = [
            'ID Pemesanan',
            'Pemohon',
            'Departemen',
            'Lokasi Asal',
            'Lokasi Tujuan',
            'Tanggal & Jam Mulai',
            'Tanggal & Jam Selesai',
            'Tujuan Penggunaan',
            'Jenis Kendaraan',
            'Jumlah Penumpang',
            'Status',
            'Kendaraan',
            'Pengemudi'
        ];
        
        // Tulis header
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $column++;
        }
        
        // Ambil data
        $data = $this->getData();
        $row = 2; // Mulai dari baris kedua
        
        foreach($data as $item) {
            $sheet->setCellValue('A' . $row, 'REQ-' . str_pad($item->request_id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $item->requester->name ?? '-');
            $sheet->setCellValue('C' . $row, $item->requester->department->department_name ?? '-');
            $sheet->setCellValue('D' . $row, $item->pickupLocation->location_name ?? '-');
            $sheet->setCellValue('E' . $row, $item->destinationLocation->location_name ?? '-');
            $sheet->setCellValue('F' . $row, $item->pickup_datetime ? Carbon::parse($item->pickup_datetime)->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('G' . $row, $item->return_datetime ? Carbon::parse($item->return_datetime)->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('H' . $row, $item->purpose ?? '-');
            $sheet->setCellValue('I' . $row, $item->vehicleType->type_name ?? '-');
            $sheet->setCellValue('J' . $row, $item->passenger_count ?? 0);
            $sheet->setCellValue('K' . $row, $item->status === 'completed' ? 'Disetujui' : 'Ditolak');
            $sheet->setCellValue('L' . $row, $item->assignment && $item->assignment->vehicle ? $item->assignment->vehicle->registration_number : '-');
            $sheet->setCellValue('M' . $row, $item->assignment && $item->assignment->driver ? $item->assignment->driver->name : '-');
            $row++;
        }
        
        // Auto-size kolom
        foreach (range('A', 'M') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Redirect output ke browser client
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="laporan-pemesanan-kendaraan-' . Carbon::now()->format('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    /**
     * Mendapatkan data
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getData()
    {
        $query = VehicleRequest::with([
            'requester', 
            'requester.department', 
            'vehicleType',
            'pickupLocation', 
            'destinationLocation',
            'assignment',
            'assignment.vehicle',
            'assignment.driver'
        ]);

        // Filter pemesanan yang sudah diproses (completed atau rejected)
        $query->whereIn('status', ['completed', 'rejected']);
        
        // Filter berdasarkan tanggal
        if ($this->request->has('start_date') && !empty($this->request->start_date)) {
            $query->whereDate('pickup_datetime', '>=', $this->request->start_date);
        }
        
        if ($this->request->has('end_date') && !empty($this->request->end_date)) {
            $query->whereDate('return_datetime', '<=', $this->request->end_date);
        }
        
        // Filter berdasarkan departemen
        if ($this->request->has('department_id') && !empty($this->request->department_id)) {
            $query->whereHas('requester.department', function ($q) {
                $q->where('department_id', $this->request->department_id);
            });
        }
        
        // Filter berdasarkan kendaraan
        if ($this->request->has('vehicle_id') && !empty($this->request->vehicle_id)) {
            $query->whereHas('assignment', function ($q) {
                $q->where('vehicle_id', $this->request->vehicle_id);
            });
        }
        
        // Filter berdasarkan status (completed/rejected)
        if ($this->request->has('status') && !empty($this->request->status) && in_array($this->request->status, ['completed', 'rejected'])) {
            $query->where('status', $this->request->status);
        }

        return $query->orderBy('pickup_datetime', 'desc')->get();
    }
} 