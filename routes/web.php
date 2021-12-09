<?php

use Illuminate\Support\Facades\Route;
use App\Models\championlist;
use App\Models\champion;
use App\Models\item;
use App\Models\synergy;
use Illuminate\Support\Facades\Storage;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/crawlhero', function () {
    $namelist = championlist::select('name')->get();
    foreach ($namelist as $key => $value) {
        $client = new Client();
        $crawler = $client->request('GET', 'https://lolchess.gg/champions/set6/'.strtolower($value['ten']).'');
        $heroname = $crawler->filter('.guide-champion-detail__name')->text();
        $price = explode(" ", $crawler->filter('.guide-champion-detail__stats__value')->text())[0];
        $toc = [];
        $crawler->filter('.guide-champion-detail__stats__row')->eq(1)->filter('.guide-champion-detail__stats__value img')->each(function ($node) use (&$toc)
        {
            array_push($toc,$node->attr('alt'));
        });
        $he = [];
        $crawler->filter('.guide-champion-detail__stats__row')->eq(2)->filter('.guide-champion-detail__stats__value img')->each(function ($node) use (&$he)
        {
            array_push($he,$node->attr('alt'));
        });
        $skillname = $crawler->filter('.guide-champion-detail__skill > strong')->text();
        $img_skill_link = $crawler->filter('.guide-champion-detail__skill__icon')->attr('src');
        $passiveoractive = $crawler->filter('.guide-champion-detail__skill > .text-gray > span')->eq(0)->text();
        $skilldescription = $crawler->filter('.guide-champion-detail__skill > span')->text();
        $mana = $crawler->filter('.guide-champion-detail__skill > .text-gray > span')->eq(2)->text();
        $skilldetails = [];
        $crawler->filter('.guide-champion-detail__skill__stats div')->each(function ($node) use(&$skilldetails)
        {
            array_push($skilldetails, $node->text());
        });

        $newchampion = new champion;
        $newchampion->name = $heroname;
        $newchampion->price = $price;
        $newchampion->toc = implode(",", $toc);
        $newchampion->he = implode(",", $he);
        $newchampion->skillname = $skillname;
        $newchampion->img_skill_link = $img_skill_link;
        $newchampion->passiveoractive = $passiveoractive;
        $newchampion->skilldescription = $skilldescription;
        $newchampion->mana = $mana;
        $newchampion->skilldetails = implode(",", $skilldetails);
        $newchampion->save();
    }
    return ;
});

Route::get('/champions', function () {
    return champion::all();
});

Route::get('/crawlitem', function () {
    $client = new Client();
    $crawler = $client->request('GET', 'https://lolchess.gg/items/set6');
    $crawler->filter('.guide-items__combine-table-wrapper > div')->each(function ($node) {
        $node->filter('.guide-items__combine-table__item')->each(function ($childnode) {
            $name = $childnode->filter('img')->attr('alt');
            $img_item = $childnode->filter('img')->attr('src');

            $new = new item;
            $new->name = $name;
            $new->img_item = $img_item;
            $new->save();
        });
    });
});

Route::get('/items', function () {
    return item::distinct('id')->get(['name', 'img_item', 'type']);
});

Route::get('/crawlhetoc', function () {
    $client = new Client();
    $crawler = $client->request('GET', 'https://lolchess.gg/synergies/set6');
    $crawler->filter('.guide-synergy-table__synergy')->each(function ($node) {
        $img_link = "//lolchess.gg". $node->filter('.guide-synergy-table__synergy__header img')->attr('src');
        $name = $node->filter('.guide-synergy-table__synergy__header img')->attr('alt');
        if ($node->filter('.guide-synergy-table__synergy__desc.mb-2')) {
            $description = $node->filter('.guide-synergy-table__synergy__desc.mb-2')->text('');
        }
        $arr = [];
        $node->filter('.guide-synergy-table__synergy__stats div')->each(function ($node2) use(&$arr) {
            array_push($arr, substr($node2->text(),1,1));
        });
        $state = implode("hehe",$arr);

        $arr2 = [];
        $node->filter('.guide-synergy-table__synergy__stats div')->each(function ($node2) use(&$arr2) {
            array_push($arr2, $node2->text());
        });
        $level = implode("hehe",$arr2);

        $new = new synergy;
        $new->name = $name;
        $new->img_link = $img_link;
        $new->description = $description;
        $new->state = $state;
        $new->level = $level;
        $new->save();
    });
});

Route::get('/synergies', function () {
    return synergy::all();
});
  
Route::get('/recommenditem', function () {
    $client = new client();
    $namelist = championlist::get('name');
    foreach ($namelist as $key => $value) {
        $crawler = $client->request('GET','https://lolchess.gg/champions/set6/'.$value->name);
        $itemArr = [];
        $crawler->filter('.guide-champion-detail__recommend-items__content > div > img')->each(function($node) use(&$itemArr) {
            array_push($itemArr, $node->attr('src'));
        });
        $itemString = implode(",", $itemArr)."<br/>";

        champion::where('name', $value->name)
        ->update(['recommend_item' => $itemString]);
    }
});

