<?php

namespace App\Exports;

use Illuminate\Http\Request;

use App\Models\Liabilities;
use App\Models\LiabilitiesLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Auth;

class LiabilitiesExport implements FromCollection, WithHeadings, WithEvents
{

    protected $liabilityId;
    protected $lang;
    protected $from;
    protected $to;

    function __construct($liabilityId,$lang,$from,$to) {
        $this->liabilityId = $liabilityId;
        $this->lang = $lang;
        $this->from = $from;
        $this->to = $to;
    }

    // set the headings
    public function headings(): array
    {
        if($this->lang == 'ar'){
            return [
                'اسم الالتزام','القيمة','التاريخ','الملاحظات','اجمالي مبلغ الالتزام','اجمالي المدفوع'
            ];
        }else{
            return [
                'Liability','value','date','notes','liability total value','Total paid value'
            ];
        }
    }

    // freeze the first row with headings
    public function registerEvents(): array
    {
        return [            
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2', 'A2');
            },
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = Auth::user();
        
        if($this->from == 'a' && $this->to == 'a' && $this->liabilityId == 'a'){
            $data = [];
            $Liabilities = Liabilities::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($Liabilities as $row){
                $liab = Liabilities::find($row['id']);
                $result = LiabilitiesLog::where('liability_id',$row['id'])->orderBy('created_at','desc')->get();
                foreach ($result as $liability) {
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم الالتزام' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                            'القيمة' => $liability['value'] == 0 ? '0' : $liability['value'],
                            'التاريخ' => date($liability['created_at']),
                            'الملاحظات' => $liability['notes'],
                            'اجمالي مبلغ الالتزام' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                            'اجمالي المدفوع' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Liability' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                            'value' => $liability['value'] == 0 ? '0' : $liability['value'],
                            'date' => date($liability['created_at']),
                            'notes' => $liability['notes'],
                            'liability total value' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                            'Total paid value' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['date'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);
                    }
                }
            }
        }elseif($this->liabilityId == 'a'){
            $data = [];
            $Liabilities = Liabilities::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($Liabilities as $row){
                $liab = Liabilities::find($row['id']);
                $result = LiabilitiesLog::where('liability_id',$row['id'])
                    ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                    ->orderBy('created_at','desc')
                    ->get();
                foreach ($result as $liability) {
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم الالتزام' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                            'القيمة' => $liability['value'] == 0 ? '0' : $liability['value'],
                            'التاريخ' => date($liability['created_at']),
                            'الملاحظات' => $liability['notes'],
                            'اجمالي مبلغ الالتزام' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                            'اجمالي المدفوع' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Liability' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                            'value' => $liability['value'] == 0 ? '0' : $liability['value'],
                            'date' => date($liability['created_at']),
                            'notes' => $liability['notes'],
                            'liability total value' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                            'Total paid value' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['date'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);
                    }
                }
            }
        }elseif($this->from == 'a' && $this->to == 'a'){
            $result = LiabilitiesLog::where('liability_id',$this->liabilityId)
             ->orderBy('created_at','desc')
            ->get();
            $liab = Liabilities::find($this->liabilityId);
            $data = [];
            foreach ($result as $liability) {
                $data[] = $this->lang == 'ar' ? array(
                    'Liability' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                    'value' => $liability['value'] == 0 ? '0' : $liability['value'],
                    'date' => date($liability['created_at']),
                    'notes' => $liability['notes'],
                    'liability total value' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                    'Total paid value' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                ) : array(
                    'اسم الالتزام' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                    'القيمة' => $liability['value'] == 0 ? '0' : $liability['value'],
                    'التاريخ' => date($liability['created_at']),
                    'الملاحظات' => $liability['notes'],
                    'اجمالي مبلغ الالتزام' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                    'اجمالي المدفوع' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                );
            }
        }else{
            $result = LiabilitiesLog::where('liability_id',$this->liabilityId)
                ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                ->orderBy('created_at','desc')
                ->get();
                $liab = Liabilities::find($this->liabilityId);
                $data = [];
                foreach ($result as $liability) {
                    $data[] = $this->lang == 'ar' ? array(
                        'Liability' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                        'value' => $liability['value'] == 0 ? '0' : $liability['value'],
                        'date' => date($liability['created_at']),
                        'notes' => $liability['notes'],
                        'liability total value' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                        'Total paid value' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                    ) : array(
                        'اسم الالتزام' => $this->lang == 'ar' ? $liab['name_ar'] : $liab['name_en'],
                        'القيمة' => $liability['value'] == 0 ? '0' : $liability['value'],
                        'التاريخ' => date($liability['created_at']),
                        'الملاحظات' => $liability['notes'],
                        'اجمالي مبلغ الالتزام' => $liab['total_value'] == 0 ? '0' : $liab['total_value'],
                        'اجمالي المدفوع' => $liab['paid_value'] == 0 ? '0' : $liab['paid_value'],
                    );
                }
        }

        return collect($data);
    }
}