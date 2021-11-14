<?php

namespace App\Exports;

use Illuminate\Http\Request;

use App\Models\Expenses;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Auth;

class ExpensesExport implements FromCollection, WithHeadings, WithEvents
{

    protected $expenseType;
    protected $lang;
    protected $from;
    protected $to;

    function __construct($expenseType,$lang,$from,$to) {
        $this->expenseType = $expenseType;
        $this->lang = $lang;
        $this->from = $from;
        $this->to = $to;
    }

    // set the headings
    public function headings(): array
    {
        if($this->lang == 'ar'){
            return [
                'اسم النفقة','النوع','القيمة','التاريخ','الملاحظات'
            ];
        }else{
            return [
                'Expense name','Type','Value','Date','Notes'
            ];
        }
    }

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
        
        if($this->from == 'a' && $this->to == 'a' && $this->expenseType == 'a'){
            $data = [];
            $result = Expenses::where('user_id',$user->id)->orderBy('created_at','desc')->get();
            foreach ($result as $expense) {
                if($expense['type'] == 'basic'){
                    $type = $this->lang == 'ar' ? 'اساسية' : $expense['type'];
                }elseif($expense['type'] == 'extras'){
                    $type = $this->lang == 'ar' ? 'اضافية' : $expense['type'];
                }
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم النفقة' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'النوع' => $type,
                        'القيمة' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'التاريخ' => date($expense['created_at']),
                        'الملاحظات' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Expense name' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'type' => $type,
                        'value' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'date' => date($expense['created_at']),
                        'notes' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['date'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);
                }
            }
        }elseif($this->expenseType == 'a'){
            $data = [];
            $result = Expenses::where('user_id',$user->id)
            ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
            ->orderBy('created_at','desc')
            ->get();
            foreach ($result as $expense) {
                if($expense['type'] == 'basic'){
                    $type = $this->lang == 'ar' ? 'اساسية' : $expense['type'];
                }elseif($expense['type'] == 'extras'){
                    $type = $this->lang == 'ar' ? 'اضافية' : $expense['type'];
                }
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم النفقة' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'النوع' => $type,
                        'القيمة' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'التاريخ' => date($expense['created_at']),
                        'الملاحظات' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Expense name' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'type' => $type,
                        'value' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'date' => date($expense['created_at']),
                        'notes' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['date'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);
                }
            }
        }elseif($this->from == 'a' && $this->to == 'a'){
            $result = Expenses::where('type',$this->expenseType)
            ->orderBy('created_at','desc')
            ->get();
            $data = [];
            foreach ($result as $expense) {
                if($expense['type'] == 'basic'){
                    $type = $this->lang == 'ar' ? 'اساسية' : $expense['type'];
                }elseif($expense['type'] == 'extras'){
                    $type = $this->lang == 'ar' ? 'اضافية' : $expense['type'];
                }
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم النفقة' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'النوع' => $type,
                        'القيمة' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'التاريخ' => date($expense['created_at']),
                        'الملاحظات' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Expense name' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'type' => $type,
                        'value' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'date' => date($expense['created_at']),
                        'notes' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['date'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);
                }
            }
        }else{
            $result = Expenses::where('type',$this->expenseType)
                ->whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                 ->orderBy('created_at','desc')
                ->get();
            $data = [];
            foreach ($result as $expense) {
                if($expense['type'] == 'basic'){
                    $type = $this->lang == 'ar' ? 'اساسية' : $expense['type'];
                }elseif($expense['type'] == 'extras'){
                    $type = $this->lang == 'ar' ? 'اضافية' : $expense['type'];
                }
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم النفقة' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'النوع' => $type,
                        'القيمة' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'التاريخ' => date($expense['created_at']),
                        'الملاحظات' => $expense['notes'],
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Expense name' => $this->lang == 'ar' ? $expense['name_ar'] : $expense['name_en'],
                        'type' => $type,
                        'value' => $expense['value'] == 0 ? '0' : $expense['value'],
                        'date' => date($expense['created_at']),
                        'notes' => $expense['notes'],
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

        return collect($data);
    }
}