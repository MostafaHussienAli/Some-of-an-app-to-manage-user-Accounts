<?php

namespace App\Exports;

use Illuminate\Http\Request;

use App\Models\Savings;
use App\Models\SavingsLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Auth;

class SavingsExport implements FromCollection, WithHeadings, WithEvents
{

    protected $savingId;
    protected $lang;
    protected $from;
    protected $to;

    function __construct($savingId,$lang,$from,$to) {
        $this->savingId = $savingId;
        $this->lang = $lang;
        $this->from = $from;
        $this->to = $to;
    }

    // set the headings
    public function headings(): array
    {
        if($this->lang == 'ar'){
            return [
                'اسم حساب التوفير','القيمة','النوع','التاريخ','الملاحظات','اجمالي مبلغ حساب التوفير'
            ];
        }else{
            return [
                'Saving account name','value','type','date','notes','Saving account total money'
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
        
        if($this->from == 'a' && $this->to == 'a' && $this->savingId == 'a'){
            $data = [];
            $savings = Savings::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($savings as $row){
                $sav = Savings::find($row['id']);
                $result = SavingsLog::where('saving_id',$row['id'])->orderBy('created_at','desc')->get();
                foreach ($result as $saving) {
                    if($saving['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $saving['type'];
                    }elseif($saving['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $saving['type'];
                    }
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم حساب التوفير' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                            'القيمة' => $saving['value'] == 0 ? '0' : $saving['value'],
                            'النوع' => $type,
                            'التاريخ' => date($saving['created_at']),
                            'الملاحظات' => $saving['notes'],
                            'اجمالي مبلغ حساب التوفير' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Saving account name' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                            'value' => $saving['value'] == 0 ? '0' : $saving['value'],
                            'type' => $type,
                            'date' => date($saving['created_at']),
                            'notes' => $saving['notes'],
                            'Saving account total money' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
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
        }elseif($this->savingId == 'a'){
            $data = [];
            $savings = Savings::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($savings as $row){
                $sav = Savings::find($row['id']);
                $result = SavingsLog::where('saving_id',$row['id'])
                    ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                    ->orderBy('created_at','desc')
                    ->get();
                foreach ($result as $saving) {
                    if($saving['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $saving['type'];
                    }elseif($saving['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $saving['type'];
                    }
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم حساب التوفير' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                            'القيمة' => $saving['value'] == 0 ? '0' : $saving['value'],
                            'النوع' => $type,
                            'التاريخ' => date($saving['created_at']),
                            'الملاحظات' => $saving['notes'],
                            'اجمالي مبلغ حساب التوفير' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Saving account name' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                            'value' => $saving['value'] == 0 ? '0' : $saving['value'],
                            'type' => $type,
                            'date' => date($saving['created_at']),
                            'notes' => $saving['notes'],
                            'Saving account total money' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
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
            $result = SavingsLog::where('saving_id',$this->savingId)
             ->orderBy('created_at','desc')
            ->get();
            $sav = Savings::find($this->savingId);
            $data = [];
            foreach ($result as $saving) {
                if($saving['type'] == 'addition'){
                    $type = $this->lang == 'ar' ? 'اضافة' : $saving['type'];
                }elseif($saving['type'] == 'subtraction'){
                    $type = $this->lang == 'ar' ? 'خصم' : $saving['type'];
                }
                $data[] = $this->lang == 'ar' ? array(
                    'Saving account name' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                    'value' => $saving['value'] == 0 ? '0' : $saving['value'],
                    'type' => $type,
                    'date' => date($saving['created_at']),
                    'notes' => $saving['notes'],
                    'Saving account total money' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                ) : array(
                    'اسم حساب التوفير' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                    'القيمة' => $saving['value'] == 0 ? '0' : $saving['value'],
                    'النوع' => $type,
                    'التاريخ' => date($saving['created_at']),
                    'الملاحظات' => $saving['notes'],
                    'اجمالي مبلغ حساب التوفير' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                );
            }
        }else{
            $result = SavingsLog::where('saving_id',$this->savingId)
                ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                 ->orderBy('created_at','desc')
                ->get();
                $sav = Savings::find($this->savingId);
                $data = [];
                foreach ($result as $saving) {
                    if($saving['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $saving['type'];
                    }elseif($saving['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $saving['type'];
                    }
                    $data[] = $this->lang == 'ar' ? array(
                        'Saving account name' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                        'value' => $saving['value'] == 0 ? '0' : $saving['value'],
                        'type' => $type,
                        'date' => date($saving['created_at']),
                        'notes' => $saving['notes'],
                        'Saving account total money' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                    ) : array(
                        'اسم حساب التوفير' => $this->lang == 'ar' ? $sav['name_ar'] : $sav['name_en'],
                        'القيمة' => $saving['value'] == 0 ? '0' : $saving['value'],
                        'النوع' => $type,
                        'التاريخ' => date($saving['created_at']),
                        'الملاحظات' => $saving['notes'],
                        'اجمالي مبلغ حساب التوفير' => $sav['total_money'] == 0 ? '0' : $sav['total_money'],
                    );
                }
        }

        return collect($data);
    }
}