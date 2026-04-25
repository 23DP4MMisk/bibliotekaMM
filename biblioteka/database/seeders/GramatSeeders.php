<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GramatSeeders extends Seeder
{
    public function run(): void{
        DB::table('Gramata')->insertOrIgnore([
            // Akademiskas gramatas
            [
             'ISBN' => '12345623',
             'nosaukums' => 'Algebra and Trigonometry 2e.',
             'gads' => '2021',
             'apraksts' => 'Algebra and Trigonometry 2e ir bezmaksas tiešsaistes mācību grāmata no OpenStax, 
             kas skaidro algebras un trigonometrijas pamatus un padziļinātas tēmas. 
             Tā aptver funkcijas, vienādojumus, grafikus, trigonometriskās funkcijas, 
             identitātes un pielietojumus. Grāmata paredzēta vidusskolas un koledžas 
             līmeņa studentiem un ietver daudzus piemērus un uzdevumus pašpārbaudei.',
             'lapu_skaits' => '1514',
             'faila_pdf' => 'uploids/books/12345623.pdf',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'Zanra_ID' => 1,
             'vaku_attels' => 'uploids/cover/12345623.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345723',
             'nosaukums' => 'Principles of Economics 3e',
             'gads' => '2022',
             'apraksts' => 'Principles of Economics 3e ir trešā izdevuma mācību grāmata par ekonomikas pamatprincipiem, 
             ko bez maksas nodrošina OpenStax — tā aptver gan mikro-, gan makroekonomikas pamatus, 
             lietojot vienkāršu valodu, piemērus un ilustrācijas, lai palīdzētu studentiem apgūt 
             ekonomikas teoriju un pielietojumu.',
             'lapu_skaits' => '975',
             'faila_pdf' => 'uploids/books/12345723.pdf',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'Zanra_ID' => 2,
             'vaku_attels' => 'uploids/cover/12345723.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345823',
             'nosaukums' => 'Introduction to Python Programming',
             'gads' => '2024',
             'apraksts' => 'Introduction to Python Programming ir bezmaksas tiešsaistes mācību grāmata no OpenStax, 
             kas iepazīstina ar Python programmēšanas valodu un pamata programmēšanas koncepcijām, piemēram, 
             mainīgajiem, datu tipiem, kontrolstruktūrām (ja-teikumos un ciklos), funkcijām, moduļiem, sarakstiem, 
             klasēm un rekursiju. Tā ir paredzēta pilnīgiem iesācējiem un satur praktiskus piemērus, 
             uzdevumus un interaktīvus materiālus, kas palīdz apgūt kodēšanas prasmes teorijā un praksē.',
             'lapu_skaits' => '406',
             'faila_pdf' => 'uploids/books/12345823.pdf',
             'autors' => 'Jay Abramson',
             'Nodala_ID'=> 1,
             'Zanra_ID' => 3,
             'vaku_attels' => 'uploids/cover/12345823.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345923',
             'nosaukums' => 'Principles of Data Science',
             'gads' => '2025',
             'apraksts' => 'Principles of Data Science ir bezmaksas tiešsaistes mācību grāmata no OpenStax, 
             kas sniedz ievadu datu zinātnē (data science) — tā aptver datu vākšanu un apstrādi, statistisko analīzi, 
             algoritmus, mašīnmācīšanos, Python izmantošanu, datu vizualizāciju, pārskatu sagatavošanu un profesionālo 
             ētiku. Grāmata paredzēta bakalaura līmeņa studentiem vai ikvienam, kas vēlas apgūt datu zinātnes pamatus',
             'lapu_skaits' => '557',
             'faila_pdf' => 'uploids/books/12345923.pdf',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'Zanra_ID' => 4,
             'vaku_attels' => 'uploids/cover/12345923.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            // Atputas gramatas
            [
             'ISBN' => '19364225',
             'nosaukums' => 'Valmieras puikas',
             'gads' => '1936',
             'apraksts' => 'Valmieras puikas ir latviešu rakstnieka Pāvila Rozīša vēsturiskais romāns, kurā 
             atainota dzīve mazpilsētā Valmierā 20. gadsimta sākumā — galvenokārt skolēnu skatījumā, pirms 
             1905. gada revolūcijas notikumiem. Stāsts atklāj jauniešu mācības, diskusijas par idejām, 
             draudzības un izaicinājumus sarežģītā sabiedriskā laikā, vienlaikus iedvesmojot domāt 
             par personīgo un sabiedrisko brīvību. Tajā ir daudz autobiogrāfisku motīvu, 
             spilgti raksturi un tēlaini aprakstīta Valmiera un Gauja.',
             'lapu_skaits' => '404',
             'faila_pdf' => 'uploids/books/19364225.pdf',
             'autors' => 'Pāvils Rozītis',
             'Nodala_ID' => 2,
             'Zanra_ID' => 5,
             'vaku_attels' => 'uploids/cover/19364225.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19404325',
             'nosaukums' => 'Sūni ciema ziema',
             'gads' => '1940',
             'apraksts' => 'Sūnu ciema zēni ir latviešu rakstnieka Andreja Upīša (1877–1970) garāks prozas darbs 
             jauniešiem un bērniem, kas attēlo Sūnu Ciema iedzīvotāju dzīvi, to tikumus un netikumus
              — slikto ieradumu un māņticības pārvarēšanu. Stāsts parāda, kā trīs puikas 
              (piem., Ješka un viņa draugi) dodas meklēt Laimes lāci, lai iedvesmotu ciematu 
              uzlabot savu dzīvi un darbību, tā simboliski uzsverot, ka laime un veiksme ir pašu 
              cilvēku rokās, nevis brīnumos.',
              'lapu_skaits' => '220',
              'faila_pdf' => 'uploids/books/19404325.pdf',
             'autors' => 'Andrejs Upīts',
             'Nodala_ID' => 2,
             'Zanra_ID' => 6,
             'vaku_attels' => 'uploids/cover/19404325.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19734525',
             'nosaukums' => 'Krāsainas pasakas',
             'autors' => 'Imants Ziedonis',
             'gads' => '1973',
             'apraksts' => 'Krāsainas pasakas ir bērnu pasaku krājums no latviešu rakstnieka Imanta Ziedoņa, 
             kas piedāvā dažādas pasakas par krāsām un to tēliem, rosina iztēli un priecē ar spilgtu 
             valodu un simboliem. Pasakas ir piemērotas gan bērniem, gan pieaugušajiem, 
             kas grib izbaudīt sirreālu un krāsainu stāstu pasauli',
             'lapu_skaits' => '80',
             'faila_pdf' => 'uploids/books/19734525.pdf',
             'Nodala_ID' => 2,
             'Zanra_ID' => 5,
             'vaku_attels' => 'uploids/cover/19734525.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19804425',
             'nosaukums' => 'Mērnieku laiki',
             'gads' => '1980',
             'apraksts' => 'Romāna Mērnieku laiki galvenā sižeta līnija ir zemes mērīšana 19. gs Piebalgā 
             kas kalpo par fonu latviešu lauku sabiedrības attēlojumam tajā tiek atainotas divas zemnieku dzimtas 
             Gaitiņi un Oļiņi un konflikti kas rodas mērnieku ierašanās dēļ Galvenās sižeta līnijas ir 
             Gaitiņu apmešanās Slātavā un izraidīšana mājās intrigantu rezultātā Kaspara un Lienas mīlestība 
             kas beidzas traģiski ar Lienas pašnāvību Lauku iedzīvotāju reakcija uz mērnieku darbu un ar to 
             saistītie konflikti un otrajā un trešajā daļā papildus kriminālintriga ietekmēta 
             no 19 gs blēžu romāniem Romāns sastāv no trim daļām un balstās uz autora novērojumiem 
             no 1867 līdz 1873 gadam',
             'lapu_skaits' => '544',
             'faila_pdf' => 'uploids/books/19804425.pdf',
             'autors' => 'Reinis Kaudzīte un Matīss Kaudzīte',
             'Nodala_ID' => 2,
             'Zanra_ID' => 6,
             'vaku_attels' => 'uploids/cover/19804425.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
        ]);
    }
    
}