<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Prize;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Sppiner extends Component
{
    public $prizes;
    public $rewardCounts;

    public $rotationValues = [];

    public $data = [];
    public $labels = [];

    public $probabilityRanges = [];
    public $radom;
    /**
     * @var mixed|null
     */
    public $customer_id;
    /**
     * @var mixed|null
     */
    public $customer_name;
    public $customer_phone;
    public int $step = 1;

    public function mount()
    {
        $this->prizes = Prize::all()->map(function ($prize) {
            $awardedCount = Reward::where('prize_id', $prize->id)->count();
            $remainingChance = $prize->chance - $awardedCount;

            return [
                'id' => $prize->id,
                'title' => $prize->title,
                'remainingChance' => $remainingChance
            ];
        })->toArray();


        $anglePerPrize = 360 / count($this->prizes); // Assuming all prizes occupy equal angles
        $currentAngle = 0;

        foreach ($this->prizes as $prize) {
            $this->rotationValues[] = [
                'minDegree' => $currentAngle,
                'maxDegree' => $currentAngle + $anglePerPrize - 1,
                'id' => $prize['id'],
                'value' => $prize['title'] // or you can use other attributes if needed
            ];
            $currentAngle += $anglePerPrize;
            $this->data[] = 16;
            $this->labels[] = $prize['title'];
        }

    }

    public function render()
    {
        return view('livewire.sppiner');
    }

    public function storeCustomer()
    {
        $customer = Customer::firstOrCreate([
            'name' => $this->customer_name,
            'phone' => $this->customer_phone,
        ]);

        $this->customer_id = $customer->id;
        $this->step = 2;
    }

    public function storeWinner($winnerValue) {
        Reward::create([
            'prize_id' => $winnerValue,
            'customer_id' => $this->customer_id,
        ]);
    }
}
