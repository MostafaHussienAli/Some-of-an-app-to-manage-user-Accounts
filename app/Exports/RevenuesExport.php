<?php

namespace App\Exports;

use Illuminate\Http\Request;

use App\Models\Revenues;
use App\Models\RevenuesLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Auth;

class RevenuesExport implements FromCollection, WithHeadings, WithEvents
{

    protected $revenueId;
    protected $lang;
    protected $from;
    protected $to;

    function __construct($revenueId,$lang,$from,$to) {
        $this->revenueId = $revenueId;
        $this->lang = $lang;
        $this->from = $from;
        $this->to = $to;
    }

    // set the headings
    public function headings(): array
    {
        if($this->lang == 'ar'){
            return [
                'اسم حساب العوائد','القيمة','النوع','التاريخ','الملاحظات','اجمالي مبلغ حساب العوائد'
            ];
        }else{
            return [
                'Revenue account name','value','type','date','notes','Revenue account total money'
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
        
        if($this->from == 'a' && $this->to == 'a' && $this->revenueId == 'a'){
            $data = [];
            $revenues = Revenues::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($revenues as $row){
                $rev = Revenues::find($row['id']);
                $result = RevenuesLog::where('revenue_id',$row['id'])->orderBy('created_at','desc')->get();
                foreach ($result as $revenue) {
                    if($revenue['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $revenue['type'];
                    }elseif($revenue['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $revenue['type'];
                    }
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم حساب العوائد' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                            'القيمة' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                            'النوع' => $type,
                            'التاريخ' => date($revenue['created_at']),
                            'الملاحظات' => $revenue['notes'],
                            'اجمالي مبلغ حساب العوائد' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Revenue account name' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                            'value' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                            'type' => $type,
                            'date' => date($revenue['created_at']),
                            'notes' => $revenue['notes'],
                            'Revenue account total money' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
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
        }elseif($this->revenueId == 'a'){
            $data = [];
            $revenues = Revenues::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach($revenues as $row){
                $rev = Revenues::find($row['id']);
                $result = RevenuesLog::where('revenue_id',$row['id'])
                    ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                    ->orderBy('created_at','desc')
                    ->get();
                foreach ($result as $revenue) {
                    if($revenue['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $revenue['type'];
                    }elseif($revenue['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $revenue['type'];
                    }
                    if($this->lang == 'ar'){
                        $data[] = array(
                            'اسم حساب العوائد' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                            'القيمة' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                            'النوع' => $type,
                            'التاريخ' => date($revenue['created_at']),
                            'الملاحظات' => $revenue['notes'],
                            'اجمالي مبلغ حساب العوائد' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                        );
                        $dateArr = array();
                        foreach ($data as $key => $row)
                        {
                            $dateArr[$key] = $row['التاريخ'];
                        }
                        array_multisort($dateArr, SORT_DESC, $data);                        
                    }else{
                        $data[] = array(
                            'Revenue account name' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                            'value' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                            'type' => $type,
                            'date' => date($revenue['created_at']),
                            'notes' => $revenue['notes'],
                            'Revenue account total money' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
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
            $result = RevenuesLog::where('revenue_id',$this->revenueId)
             ->orderBy('created_at','desc')
            ->get();
            $rev = Revenues::find($this->revenueId);
            $data = [];
            foreach ($result as $revenue) {
                if($revenue['type'] == 'addition'){
                    $type = $this->lang == 'ar' ? 'اضافة' : $revenue['type'];
                }elseif($revenue['type'] == 'subtraction'){
                    $type = $this->lang == 'ar' ? 'خصم' : $revenue['type'];
                }
                $data[] = $this->lang == 'ar' ? array(
                    'Revenue account name' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                    'value' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                    'type' => $type,
                    'date' => date($revenue['created_at']),
                    'notes' => $revenue['notes'],
                    'Revenue account total money' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                ) : array(
                    'اسم حساب العوائد' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                    'القيمة' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                    'النوع' => $type,
                    'التاريخ' => date($revenue['created_at']),
                    'الملاحظات' => $revenue['notes'],
                    'اجمالي مبلغ حساب العوائد' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                );
            }
        }else{
            $result = RevenuesLog::where('revenue_id',$this->revenueId)
                ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                 ->orderBy('created_at','desc')
                ->get();
                $rev = Revenues::find($this->revenueId);
                $data = [];
                foreach ($result as $revenue) {
                    if($revenue['type'] == 'addition'){
                        $type = $this->lang == 'ar' ? 'اضافة' : $revenue['type'];
                    }elseif($revenue['type'] == 'subtraction'){
                        $type = $this->lang == 'ar' ? 'خصم' : $revenue['type'];
                    }
                    $data[] = $this->lang == 'ar' ? array(
                        'Revenue account name' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                        'value' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                        'type' => $type,
                        'date' => date($revenue['created_at']),
                        'notes' => $revenue['notes'],
                        'Revenue account total money' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                    ) : array(
                        'اسم حساب العوائد' => $this->lang == 'ar' ? $rev['name_ar'] : $rev['name_en'],
                        'القيمة' => $revenue['value'] == 0 ? '0' : $revenue['value'],
                        'النوع' => $type,
                        'التاريخ' => date($revenue['created_at']),
                        'الملاحظات' => $revenue['notes'],
                        'اجمالي مبلغ حساب العوائد' => $rev['total_money'] == 0 ? '0' : $rev['total_money'],
                    );
                }
        }

        return collect($data);
    }
}
