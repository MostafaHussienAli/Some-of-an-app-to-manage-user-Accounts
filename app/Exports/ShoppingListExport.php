<?php

namespace App\Exports;

use Illuminate\Http\Request;

use App\Models\ShoppingList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Auth;

class ShoppingListExport implements FromCollection, WithHeadings, WithEvents
{

    protected $lang;
    protected $from;
    protected $to;

    function __construct($lang,$from,$to) {
        $this->lang = $lang;
        $this->from = $from;
        $this->to = $to;
    }

    // set the headings
    public function headings(): array
    {
        if($this->lang == 'ar'){
            return [
                'اسم قائمة التسوق','السعر','التاريخ'
            ];
        }else{
            return [
                'Shopping list name','Price','Date'
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

        if($this->from == 'a' && $this->to == 'a'){
            $result = ShoppingList::orderBy('created_at','desc')->get();
            $data = [];
            foreach ($result as $shoppingList) {
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم قائمة التسوق' => $this->lang == 'ar' ? $shoppingList['name_ar'] : $shoppingList['name_en'],
                        'السعر' => $shoppingList['Price'] == 0 ? '0' : $shoppingList['Price'],
                        'التاريخ' => date($shoppingList['created_at']),
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Shopping list name' => $this->lang == 'ar' ? $shoppingList['name_ar'] : $shoppingList['name_en'],
                        'Price' => $shoppingList['Price'] == 0 ? '0' : $shoppingList['Price'],
                        'date' => date($shoppingList['created_at']),
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
            $result = ShoppingList::whereBetween('created_at', [$this->from." 00:00:00",$this->to." 23:59:59"])
                ->orderBy('created_at','desc')
                ->get();
            $data = [];
            foreach ($result as $shoppingList) {
                if($this->lang == 'ar'){
                    $data[] = array(
                        'اسم قائمة التسوق' => $this->lang == 'ar' ? $shoppingList['name_ar'] : $shoppingList['name_en'],
                        'السعر' => $shoppingList['Price'] == 0 ? '0' : $shoppingList['Price'],
                        'التاريخ' => date($shoppingList['created_at']),
                    );
                    $dateArr = array();
                    foreach ($data as $key => $row)
                    {
                        $dateArr[$key] = $row['التاريخ'];
                    }
                    array_multisort($dateArr, SORT_DESC, $data);                        
                }else{
                    $data[] = array(
                        'Shopping list name' => $this->lang == 'ar' ? $shoppingList['name_ar'] : $shoppingList['name_en'],
                        'Price' => $shoppingList['Price'] == 0 ? '0' : $shoppingList['Price'],
                        'date' => date($shoppingList['created_at']),
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