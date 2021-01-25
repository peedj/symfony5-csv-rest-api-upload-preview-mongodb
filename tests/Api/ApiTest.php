<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiTest extends WebTestCase
{


    public function testAddAndGetData()
    {
        $this->_createUploadFile();

        $originalFileName = 'test.csv';
        $csvFile = new UploadedFile(__DIR__ . '/../../public/test/test.csv', $originalFileName, 'text/csv');

        /**
         * @var $client KernelBrowser
         */
        $client = static::createClient();
        $client->request('POST', '/api/csv', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
        ], [
            'csv_file' => $csvFile
        ]);

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $result = $response->getContent();
        $this->assertStringStartsWith('{"id":', $result);

        $array = \json_decode($result, true);
        $this->assertIsArray($array);

        $this->assertArrayHasKey('fileName', $array);
        $this->assertEquals($originalFileName, $array['fileName']);

        $this->assertArrayHasKey('id', $array);
        $this->_getData($client, $array['id']);

    }

    /**
     * @param KernelBrowser $client
     * @param string $id
     */
    public function _getData(KernelBrowser $client, string $id)
    {
        $xSeconds = 2;
        sleep($xSeconds);

        $client->request('GET', '/api/csv-data/' . $id);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $this->assertStringStartsWith('{"fileName":"test.csv","items":[', $result, 'Not Corresponding Response Returned');

        $array = \json_decode($result, true);

        $this->assertIsArray($array, 'Response is Array');

        $this->assertArrayHasKey('items', $array, "Has no Items In Response"); // has items
        $this->assertArrayHasKey(1, $array['items'], "Has no Item With Index=1 In Response"); // second row

        $this->assertEquals('Ujifu cieg jiwig icfin sovi ecudof rumaka neufekug fotzaudi azo ca cedhi picaw cudes cerismin wevvoswob.', $array['items'][1][1], 'File uploaded And Parsed And It Took Less than ' . $xSeconds . ' sec');
    }

    private function _createUploadFile()
    {
        $contents = <<<CONTENTS
Address; First Name; Last Name; Age; Phone
Henrietta Perkins;Ujifu cieg jiwig icfin sovi ecudof rumaka neufekug fotzaudi azo ca cedhi picaw cudes cerismin wevvoswob.;42;(273) 710-8795;1740 Zuzgi Drive
Max Simpson;Decagav lo lo ivkac cugalo gahi zo uheda gap ruapo udu vebuoni uk pajugba duz socu ud zuogafi.;49;(803) 970-4913;1897 Beos Center
Sadie Dennis;Vepnodig wufog udkih kioki juudlup tum upu in la now johugez nec so.;19;(725) 226-1732;1408 Reruw Grove
Ada Norman;Zalvup cu duzsiv ewkaf hewible cig unager cuton duvil cegbu uk juza arekewar dutup jefte na.;54;(744) 574-5809;944 Oneowe Way
Connor Daniel;Baldor pakboz lerriete inu muveh di hi wosdeheg asogokolu udifebjif cocimetib hoiz.;50;(218) 219-9711;883 Mokto Street
Hallie Terry;Vekov ke neha cidrier ma husweli jesepu jaok ipsek anet ge wuwovgi mida deicugu mehetma.;27;(913) 855-7046;1284 Talcon Path
Jean Myers;Sugfof rucatmu hesowoci la titin lu uvru lamob odmib tarro raw tohbatcov janafmo nujgo gezelo ripel geufi wucreuv.;42;(943) 875-6000;212 Rukow Boulevard
Bettie Miles;Ji bamumecu gizep se jojih mocujve hewcerla ihi vi jotok iv nildun po nobvor lic remagi.;64;(407) 258-8581;1501 Bote Pass
Sally Saunders;Zozuwiv iwiwez espas sab zemba teh vupor ofba hoveluzi can mefoneg ho.;63;(966) 294-5558;604 Ekeupu Loop
Floyd Bailey;Bed dekpe nutejuci iceti rahaw voh buubcub zuabizuz zadkeg lijid cug teazih.;24;(489) 210-8234;1980 Ojuru Turnpike
Cynthia Roberts;Kegup so mewug po hob bih ku dupeali ivwomhup kedwuko oda vo jujjut pirpojnec.;63;(472) 724-3093;1915 Oloru Mill
Hannah Howard;Ticirmuc wuoweho eteubu hunkaszus mih guhzagzob hurufagu idodeowi vapsar mesori koziba za bazrozi ed.;59;(489) 532-2703;783 Jufem Road
Bertha Rodriguez;Elouf atbuc of tesujbab ozre kof vo digda jeta vizra jukom oco arpakoz ha ohohu wag jiwugo gazporge.;44;(712) 745-8164;1089 Fafu Glen
Marc Malone;Evta po ohotekkat kem gokra nablav kigmubum otfih zeop geldesbi kam ornipdu sucawasob.;31;(487) 388-2781;496 Decpit Grove
Cory Harrington;Belkuana egiihman si ciwzismed jica cu fak sacmo elu gu udaavo on gadi figwegded rigfuw.;54;(869) 221-6651;1601 Pidnot Lane
Nicholas Parks;Pausauhi ga adi pehzobru den nifwora pifbu neg secvi cizbi zajud sa ji.;24;(488) 746-6875;31 Gepo Street
Elizabeth Curtis;Acadabgoh novawzut seme gumocabih mug hebuplub ilu ibgem tulceji hebwige fih jes etfa gibi corubuwep igzehi janvutrof.;53;(767) 895-2330;571 Cezab Junction
Alexander Collier;Kisoca bildalo mad vekosus jemear sorwonu figi emiugeki hostobopu se lir upte wo sekoza.;19;(385) 674-1637;1085 Tapu Parkway
Nannie McCarthy;Ru gu he ne wugle vidif wiab rofe onudodlar web zuptulim okanucna ah sikapko ojbioh gewmeiti oddi wuc.;60;(964) 809-3381;958 Codvo Heights
Jeff Hopkins;Kavre fuaceaje pehuzbu ugo zimi motugu nup ka zannotep uvura omvigi buvbe.;37;(717) 232-8561;1862 Zadha Point
Jay Wilkins;Nu uzief taza zen leej duf tido bifor uzepu dedpen pi fuj jusbi pipu.;39;(950) 670-5343;34 Vuzte Street
Maria Barrett;Rabdisa daejmin ris bon tumrusihe viruosa sehka zef op gad warelje kute damjuh atamistoh.;34;(866) 759-3903;49 Banum Drive
Isaac Bell;Ju poru alanep zafuku mezgusti wurizor fu uvibiob poshilnu tecan nedukfo ubowa tepah vir jaj zac.;30;(439) 266-6124;165 Huvniv View
Lottie Ortega;Ber kaci laglan loocwil jojuj rihun ni jipmecju kirdontir cemebu za fawu wa cub run silien nulmoih.;19;(571) 581-2266;1882 Guvpi Mill
Victor Vaughn;Juztotfi uzvauha as gazo maohiki oridap dopi medwaluro eguba ozlebhel to noaje.;29;(853) 729-8238;138 Bupos Court
Chad Stevenson;Uguw anwof foste kacuju ba ha ubi caz fasiziz emo cibo tew.;50;(649) 244-9167;66 Weuki Trail
Michael Pearson;Uz nisew habevmev imoecomuf ibajik didi ardat ho ho ci vudho cabeva gu.;39;(664) 316-1095;406 Ocme Heights
Jayden Patrick;Bocobgep idlulab gihevfug huw la savorbop kuuhjet ze bo cesso emufogur hozfi acutadaku fovkisek evmodu vafezeosi.;23;(613) 615-9133;332 Egus Road
Flora Hansen;Gi oniwuh ema beweme za zaraccu ab ok mozuzcac fukir ronecti ijiko so.;18;(619) 848-2700;246 Wezwam Terrace
Jayden Butler;Rufzak orivozar wufubci ko cijo kacusujuj soazupu funevda runpivzi wipisem taf bitte ormosop gawfur.;60;(308) 909-1536;630 Ridodu Road
Sophie Craig;Gohi amudagmak wema vavjuc iha jet rumvo pok egco pe gazgur mi.;54;(786) 757-4427;691 Giesu Point
Tom Nelson;Ro ocet taazurib nug acjasmu seodate aptop zazuat iguha docmi bemkop lek kok ug woz cilaehi peziwrov napwujpog.;42;(420) 510-1309;153 Pudul Loop
Charlotte Carlson;Navpib ahi is ros duhulucuk pi mevozu ve buw jozup vuge ajtobi.;53;(664) 222-6229;1013 Ismuf Mill
Mamie Andrews;Iclowrin towjo hic fudo sosin ohuj ebwad ijihed edaar locdi izapuheh kagulus nat inkum zapkubzoj sok je.;44;(412) 233-7634;282 Meda Trail
Lina Romero;Ri geuz zobzur dutsusti fut rohdinek fipieba tengica val de uzousher efduiti.;27;(749) 458-8696;148 Uroicu Boulevard
Randall Holloway;Olabolfet gejrim tu potpo puhsis sehub huc za kedwa patu ifhepub tehnuh jijisu fo kezus rucebi.;52;(532) 643-5119;1128 Wibal Pike
Dominic Tyler;Rocmucaz oredobah efcu opi gijuhi tofelior ucajuzi diac tibo buwviw kiemo ra cahislal mud at.;50;(606) 774-4864;683 Volef Street
Nina Bryant;Esiehizuv zi rubgi noiwi ler vuifo fozdijid jappob kisanre ba mazopehiz cupmodo un.;25;(442) 748-5856;24 Data Trail
Sam Pope;Mambo pekhek wose di eka fo luniga vudiv puvceh zibes ovada cod akehesu adagugje va cipwabit za jaj.;49;(427) 425-7290;467 Kajken Mill
Elnora Medina;Degaho do hijarho iki cele zamak pekediv tuldoj ben wusbuba hu ozgaw zurlejno hirlob pa bomol sebaaj.;32;(835) 951-8548;357 Velna Lane
Winnie Tyler;Jifuvu te lata uvde avdihak he puprubba fituvro hipos iv agohap ru edwilan nosa.;24;(966) 413-6186;30 Tuipe Turnpike
Ida Moss;Fovi murat poiz di le caj eho azunoihu toda gec facfeb isovabjiw volnaci kebep olija fop aju inma.;43;(838) 810-6830;1491 Pogaj Trail
Catherine Lloyd;Izapi du eca opopisok noki toshu ve abo hir serin acaugo ef naab id wuvir.;55;(424) 215-3582;608 Ozuva Circle
Leon Jensen;Vagnobed adhomto se ma weomu ibaulicol az nukja uznus hejtub vo ka duvutec awe torbecef ulumem.;34;(987) 898-4033;625 Vegeni Extension
Maud Hopkins;Ca roh larbus poudra obajumjuz sufrapel hol panahuz opi desi cecdesec ona aja atjucsat puswowmub dosavlu ujukezi dahewaka.;59;(818) 593-7024;1405 Ofogez Road
Nathan Osborne;Eneeled anomot devsuwvam liccugfa hargav sip avavag mefwujkac ki nonjaw wofezmal vobufuhe.;21;(315) 298-7712;1791 Safhu River
Roy Harrison;Hadga pitu wu itkow fucazebi watu sacvuwe ebdiwfa tetew mefoew lokdav uluzo janu.;61;(803) 608-3379;611 Ogoti Heights
Miguel Smith;In fiub wulunara rotgireza ce ep lumdur jehjel bep eje ubdedabe mudwoaza uk om vevigoj fazzik ja motsuve.;26;(631) 447-2886;39 Ejpi Avenue
Wayne Frank;Tit gaspulu tat zuhfe kuleora ozbu wecvafo ruh ciz tosot sosliwkin jibej devoremi minpomke mo lud bad.;35;(943) 475-2526;1981 Hilho Pike
Albert Richardson;Loha bahajufi wukniho wipovib rijoibi duza bevuzo molhav ej ammohsu jaj samwukeh.;59;(461) 794-4303;520 Webfac Glen
Elva Simpson;Ugatgiz cohbi ufhana maveb zun celi tij ru fofemof goc bodig ane ca ozanivte zoj ki zuh.;57;(749) 259-4103;1938 Fizca Extension
Barry Tate;Kobzate ges jadsoj jedap boh kitafuwu henhore opa eno fuw wego si halaw lodahe zu wo rodkugep.;44;(554) 901-3364;1936 Vemen Turnpike
Christina Hudson;Vu mihinelam of honet fol vubi ba vulib etiozebu ipjun utihizi fof.;65;(361) 224-4836;1093 Jahi Park
Roxie Henderson;Zu pipgeij utacig sihol viepuk fan ip puri lulpu rejuhbu jebil to ew ja necpuuk.;62;(256) 237-9435;785 Vizuh View
Ralph Barrett;Migjotpa ba femfilkib gage ru keh ojot belig toz koez gidilve ape paoji ja miwodsi.;26;(645) 867-9138;1888 Kunma Loop
Bess Harmon;Nofem tef dab befiaco no vog bo tipaz soipe guohiate tuzwo cisih hamduripu ekmiat halekjuw adkeawu ozmusni otuze.;62;(842) 643-3962;142 Kevga Drive
Dylan Douglas;Tazekuc zuriteku siridvi jitkewraw webed be wacaw aw poamise ofe ofo dulhu oweocetet fomocpes ozecol.;18;(375) 271-9104;1164 Hunus Center
Joel Patton;Zo kobe agoc eb vaero tuz gidcof wepnipat ri ol sece man doahugah sadzuroj.;18;(844) 338-6002;1059 Notuh Plaza
Cecelia Harris;Nohwejo av ejte ife vascem viogu nilo muvho lamawi vevusiw zupsefid muilo bopzeznu zet ime heko.;63;(362) 382-3036;689 Libra Trail
Chester Kelly;Ze naumbuk po tesgavza tonap cufno tierzof is lo rohve luti miofima wikapo.;33;(866) 486-4363;732 Pagleh Pass
Mittie Ramos;Tevoku sena sebifaw fosvosfu sat divipip num zu rado lavarki iceloow rohudteb.;36;(351) 622-1027;1094 Omoba Place
Josephine Erickson;Udomoep juw egioco al row irawejer afgon atuwuwu nuhih tulel cojuhu icita go gobo.;36;(814) 675-2150;1497 Utak Key
Philip Fleming;Nacivvos gooropam uwuhuvip lomgepube vuuli coko wafe pamiz wa ude pajrov keure ro net.;24;(205) 970-3362;1229 Kahuj Drive
Frederick Tran;Es fimtas zope ro aped bif lobezmib kahciphul zuwge but nesmazif lunrag.;32;(222) 592-7843;964 Kegmi Turnpike
Walter Diaz;Ke su fewiw fohuvi suwgocup ulgel muptagmaj nebaheg ewe fawitgeg dah totzu juzez bufi jujkeh guveid po noavo.;19;(225) 634-2375;237 Fezpu Grove
Jeremy Silva;Ahu dol seofne uziri obiime mac folruapo zajmif ubemowvu fevdifol upujihek tik.;59;(209) 595-2552;1165 Dafjul Point
Delia Lopez;Tuhip li jawale cuvmucwo munal pis dajvow guudtu cugeb kuvodlid ukaogu voza fosne te nal zefmadus rozu.;51;(743) 631-8968;1029 Baveb Circle
Lawrence Phillips;Haw akcal tucegaf vajbi uh azbulhoj kajbu dej mozo fasi pab olu.;24;(447) 203-7949;1287 Ubaomu Court
Lulu Perkins;Wez sol el gorimope geup kisno ove sahhawip iw malunga ale kumus tophen cor sumweta.;35;(441) 994-4663;1356 Sefic Heights
Loretta Phelps;Kasep oko nup zo hanu lapin naspisguk uh pevtazmu gefutoba cumugu pecafez zedu me.;58;(859) 874-5829;54 Kaner Court
Arthur Walton;Rolap gohjoc ge febe fat hiukez mafciplub nukhuc fan boetoco dovop huvepjur.;44;(384) 439-7717;995 Ufni Grove
Jeremiah Wheeler;Zocozu gu cezachi vakmowtu ubvadop ot ce vo uhizu fetiglo ow etuwovdo par uf todvabke pu mu.;20;(477) 410-6701;344 Ofaig Avenue
Luis Kelly;Os jewku baoku maldofem tovubuf gipinle vecta sugufib azpe kun cedesu nieb owiuwako jole.;26;(802) 742-2602;1661 Eknuz Lane
Jacob Lowe;Asa cuknis jokozmod im lawuv selriuda iga omijoz guebador peedo ze batsujket seg.;63;(729) 855-4777;1970 Vetu Center
Lawrence Diaz;Ej jadici hu ugudiom go zur codzavdat vediuz nevi rip gaev puafpuf esfinfo vem osipoze vaib ro.;51;(905) 523-6808;13 Bava Highway
Edith Austin;Tukemin as vibefuro mucolu somew ro azeele uj mulo huipe cohoki lepa hiega nuja poful.;22;(769) 956-6918;1480 Gihfin Key
Margaret Sanchez;Sihiif ha duc kohkufbu lusun goez fahnewve figpo bentu ba zi eruverak udidorun pahuf gumi.;64;(401) 363-8573;1974 Ochel View
Agnes Schwartz;Ife jefjiz obnenbin ogwas zofah la cecori cenoh fuk fozozi ta gajuko vucgoc fuhganej kijone efe.;21;(914) 209-4858;1579 Bowo Glen
Andrew Massey;Jobudir peko uv wa dojlijop ruhuji igwoj zevinulum wesno cajapta todrej tatho gifadom fam cokew huuvop pu he.;21;(820) 772-2135;1698 Tazwi Heights
Terry Warren;Bave uvi wodramewe eticu dulbusec opavufeb bo sormas ne socnawep utiboge ovifu aboci malseg puk jol tejiw lic.;38;(318) 750-2623;588 Ledaz Way
Jeff Gomez;Fihoke ezaliwa gakusid namu pu nukukkop ciotuzu mis wukdu pa pi ji hi we anasom vapsokbu nucvulis ilfit.;54;(776) 513-7742;94 Tufha Turnpike
Polly Richards;Usa pukaho zor zowwig vized ohi tic cooje belutu jato pu jewe des geihoaz tocap rujri fejni ihi.;37;(745) 954-7295;227 Veub Place
Harold Bennett;Hudmelrim ijowajjob zinom redan im doveper nawjar ni zebucke meoma dugu set he mafsijat beriwoezi onopebwab hulaw.;58;(831) 474-3986;1180 Pemup Court
Vernon Gill;Bumsu og oji pejigi hoczom ruun samvis eco fiplo secoh wofla he humriwor wod hen mibi rubsu.;33;(641) 811-8888;116 Durkut Plaza
Raymond Herrera;Vahlumo bedfikpiv wezulko fuuta puena nuucupa nuz rulhojoki umemo rewribaz ca octow hoc wiugtek.;30;(762) 246-2659;1916 Cipej Road
Loretta Fox;Ibdo ef degim berop geuh venedo ce run kiz gon ocigidoj judo ri sok zilag ucomoesa.;21;(365) 472-1947;353 Sumto Highway
Eugene Sutton;Fu enufdi kidcuvcat sab gis migworu di ocdeaz negus usamegew mis cosra udbe damrubwu le zuw cep.;49;(427) 729-1101;1091 Eszid Key
Harriett Ramirez;Sib vaneh ijisob sap repsuj korojjuj mooho juwunmoz mip nu zogofi gehik cihpu mefuf.;33;(358) 650-2240;845 Poaz Mill
Betty Erickson;Emedo jifaoji rev wonzad gajvo wodi oluj ifianiupe zikeddu vepo jen wene ure imweav.;42;(276) 758-4880;596 Pupa Park
Ruth Kim;Kiec lufbup pubdoc racup gobuna tedhudto fahsi veno irhel rekat gepwic kig re pevlek hu hes kot umji.;60;(355) 607-5572;888 Dashi Plaza
Alta Schmidt;Ergir bo ikiabo gig wuno uka magpucpak kivuddob du ge ikize uha dihcuhi wappen neli se.;27;(344) 852-7585;1121 Punce Terrace
Kathryn Jimenez;Edhihage akehahub giivitih fen gutjaaki dat zad wikepbow cocwutnob corvedaz san tu he egojine vop.;47;(975) 743-8081;1317 Olfub Terrace
Callie Gray;Apre bis cavtacon sajos dezkafi mifwib be votaf inihu itsivaku kizhijhoh niduh rajbu nuremhi kahsibov tobwabti pehocza.;35;(885) 551-7147;1379 Gurzot Trail
Lillie Frazier;Berlaha riv fe uceobe okirila hip merpi lafuwu cimufofo mindimsun diz mamar ros nurvirug.;57;(319) 794-9608;1831 Futov Pass
Sean Collins;Gij ib fespu foju dehtaktij hokfosduz ce epoga jukhuz vame jiwhe nafrur unu sobbatib.;64;(765) 529-8566;1199 Fobu Road
Teresa Nash;Zinir jof tazildos afo ottav ohap lefvabku otrofjac ogi koh bepaho ugfe hi.;50;(720) 362-9381;869 Foro Manor
Edward Schneider;Me ag wab ziglun nasre le ilfup zuaso we fi ku eru gurogcej vemuwabe pemlibim.;24;(752) 209-1540;763 Bueci Square
Adam Murphy;Jolgotuj utsi utfom mop iwhij da lihi ilnut jejuil weisufa mibfifrof raub uc aroikowo uvi jem.;60;(213) 220-6942;789 Noes Mill
Andrew Jackson;Zin corifa polwajec wu tigeg vova cipahac ebiwukar uli um siem sapes ceduug podu tus giaki tatameg tok.;65;(765) 703-9420;95 Ovme Road
Milton Phillips;Dod danighoz biwob duztifju igzaduh ar piolru ihufizdu ribe luderuk amlinti do makhenu uvowo cucrumga ogjegus zup.;65;(557) 527-5655;1832 Vevreh Pike

CONTENTS;

        file_put_contents(__DIR__ . '/../../public/test/test.csv', $contents);
    }

}