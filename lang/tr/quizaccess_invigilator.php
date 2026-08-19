<?php
// This file is part of Moodle invigilator for Moodle - http://moodle.org/
//
// Moodle invigilator is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle invigilator is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with MailTest.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Turkish strings for the quizaccess_invigilator plugin.
 *
 * @package    quizaccess_invigilator
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Gözetmen (Invigilator)';
$string['quizaccess_invigilator'] = 'quizaccess invigilator';
$string['invigilatorlabel'] = 'Doğrulama sürecini kabul ediyorum.';
$string['youmustagree'] = 'Devam etmeden önce kimlik doğrulama sürecini kabul etmelisiniz.';
$string['notrequired'] = 'gerekli değil';
$string['invigilatorrequiredoption'] = 'sınav başlamadan önce onaylanmalı';
$string['invigilatorrequired'] = 'Ekran görüntüsü ile gözetim doğrulaması';
$string['invigilatorrequired_help'] = 'Etkinleştirilirse, öğrenciler sınava başlamadan önce ekran görüntüsü alınmasını kabul etmek zorundadır.';
$string['warning:allowscreenshare'] = 'Lütfen ekran paylaşımına izin verin.';
$string['invigilatorheader'] = '<strong>Bu sınava devam edebilmek için ekranınızı paylaşmanız gerekir. Ekran paylaşımında tüm ekranı seçmelisiniz.</strong>';
$string['picturesreport'] = 'Gözetmen raporunu görüntüle';
$string['screensharemsg'] = '<strong>* Lütfen tüm ekran için ekran paylaşımına izin verin.</strong><br/><strong>* Bu pencereyi kapatmayın, aksi halde sınavınız sonlandırılır.</strong><br/>';
$string['screenhtml'] = '<span><video id="invigilator-video-screen" width="320" height="240" autoplay></video></span><span id="invigilator-log-screen" style="display:none;"></span><span id="invigilator-recording-status" class="invigilator-recording-status"></span>';
$string['sharescreen'] = 'Devam etmek için ekran paylaşımına izin verin';
$string['sharescreenbtnlabel'] = 'Ekranı paylaş';
$string['quizaccess_invigilator_label'] = 'Gözetmen';
$string['invigilatorreports'] = 'Gözetmen raporları';
$string['invigilatorreportsdesc'] = 'Gözetmen raporları, sınav sırasında alınan ekran görüntülerini gösterir.';
$string['dateverified'] = 'Tarih';
$string['actions'] = 'İşlem';
$string['name'] = 'Ad';
$string['screenshot'] = 'Ekran görüntüsü';
$string['notpermissionreport'] = 'Bu raporu görme yetkiniz yok.';
$string['picturesusedreport'] = 'Ekran görüntüleri';
$string['summarypagedesc'] = 'Özet rapor, her sınav ve ders için alınan ekran görüntüsü sayısını gösterir. Bir sınava/derse ait tüm görüntüleri silebilirsiniz.';
$string['settings:deleteallsuccess'] = 'Ekran görüntüleri başarıyla silindi.';
$string['reportidheader'] = 'Satır No';
$string['coursenameheader'] = 'Ders adı';
$string['quiznameheader'] = 'Sınav adı';
$string['alert:screensharemsg'] = 'Lütfen tüm ekranı paylaşın.';
$string['alert:restartattemptcommand'] = 'Ekran paylaşımını durdurduğunuz için sınavı yeniden başlatmanız gerekiyor.';
$string['alert:somethingwentwrong'] = 'Görüntü alınırken bir hata oluştu.';
$string['invigilator:bulkdelete'] = 'Gözetmen: Toplu silme';
$string['invigilator_bulkdelete'] = 'Gözetmen toplu silme';
$string['success'] = 'başarılı';
$string['invalidtype'] = 'geçersiz tür';
$string['invigilator:logs'] = 'Gözetmen kayıtları';
$string['invigilator:Logs'] = 'Gözetmen Kayıtları';
$string['imgdlt'] = 'Görüntüler silindi!';
$string['invigilator:summery'] = 'Gözetmen özet raporu';
$string['invigilator:report'] = 'Gözetmen raporu';

$string['privacy:core_files'] = 'quizaccess_invigilator ekran görüntüleri';
$string['privacy:metadata:core_files'] = 'Bu eklenti, sınav sırasında paylaşılan ekranın görüntülerini saklar.';
$string['privacy:metadata:quizaccess_invigilator_logs'] = 'Raporlama için tüm doğrulama kayıtlarını saklar.';
$string['privacy:metadata:quizaccess_invigilator_logs:userid'] = 'quizaccess_invigilator_logs tablosundaki kullanıcının ID bilgisi';
$string['privacy:metadata:quizaccess_invigilator_logs:screenshot'] = 'Sınava ait ekran görüntüsünün bağlantısı.';

// Yetkiler.
$string['invigilator:sendscreenshot'] = 'Sınav sırasında ekran görüntüsü gönderme';
$string['invigilator:getscreenshot'] = 'Sınav sırasında alınan ekran görüntülerini görme';
$string['invigilator:viewreport'] = 'Gözetmen raporunu görme';
$string['invigilator:deletescreenshot'] = 'Alınan ekran görüntülerini silme';
$string['invigilator:sendrecording'] = 'Sınav sırasında yakalanan ekran karelerini gönderme';
$string['invigilator:viewrecording'] = 'Sınav sırasında yakalanan ekran karelerini izleme';
$string['invigilator:deleterecording'] = 'Yakalanan ekran karelerini silme';

// Ekran kaydı ayarları.
$string['setting:enablerecording'] = 'Ekranı görüntüle';
$string['setting:enablerecording_desc'] = 'Kapatılırsa öğrenciler sınava başlamak için yine ekran paylaşmak zorundadır, ancak hiçbir görüntü saklanmaz.';
$string['setting:recordingwidth'] = 'Ekran görüntüsü genişliği (piksel)';
$string['setting:recordingwidth_desc'] = 'Görüntüler, paylaşılan ekranın en-boy oranı korunarak en fazla bu genişliğe küçültülür. İzin verilen aralık: 320 - 3840. Varsayılan ayarlarla bir görüntü yaklaşık 100-150 KB\'dir, yani 1 saatlik bir sınav yaklaşık 40 MB yer kaplar.';
$string['setting:recordingmaxsize'] = 'Bir ekran görüntüsünün en büyük boyutu (MB)';
$string['setting:recordingmaxsize_desc'] = 'Bu boyutu aşan görüntüler reddedilir. Yükleme base64 ile kodlandığı için görüntüden yaklaşık üçte bir oranında büyür; bu değeri sunucunun PHP post_max_size ayarının altında tutun.';
$string['setting:recordingretention'] = 'Ekran görüntülerinin saklanma süresi (gün)';
$string['setting:recordingretention_desc'] = 'Bu süreden eski görüntüler zamanlanmış görev tarafından silinir. Elle silinene kadar saklamak için 0 girin.';

// Kayıt raporu.
$string['recordingsreport'] = 'Ekran yakalamalarını görüntüle';
$string['recordingsreportdesc'] = 'Bu sınava ait tüm yakalama oturumları. Bir oturum, tek bir sınav denemesinde alınan tüm kareleri içerir ve hızlandırılmış biçimde oynatılır.';
$string['norecordings'] = 'Bu sınav için henüz ekran yakalaması saklanmamış.';
$string['recordingstart'] = 'Başlangıç';
$string['recordingduration'] = 'Süre';
$string['recordingsize'] = 'Boyut';
$string['recordingplay'] = 'Oynat';
$string['recordingdeleteconfirm'] = 'Bu yakalama oturumunun tamamı silinsin mi?';
$string['recordingdeleted'] = '{$a} kare silindi.';
$string['recordingsessionsummary'] = '{$a->frames} kare, {$a->duration} süreyi kapsıyor, başlangıç: {$a->start}.';

// Kayıt mesajları.
$string['alert:recordingunsupported'] = 'Bu tarayıcı ekran yakalayamıyor. Lütfen güncel bir Chrome, Edge veya Firefox kullanın.';
$string['alert:recordingfailed'] = 'Ekran yakalama beklenmedik şekilde durdu. Lütfen sınav boyunca bu pencereyi açık tutun.';
$string['warning:recordingdisabled'] = 'Bu sitede ekran yakalama kapalı.';
$string['warning:emptyrecording'] = 'Yakalanan kare boş olduğu için yok sayıldı.';
$string['warning:recordingtoolarge'] = 'Yakalanan kare {$a} sınırından büyük olduğu için yok sayıldı.';

// Zamanlanmış görev.
$string['task:cleanuprecordings'] = 'Süresi dolan gözetmen ekran yakalamalarını sil';

// Gizlilik.
$string['privacy:metadata:quizaccess_invigilator_rec'] = 'Sınav sırasında yakalanan ekran karelerini saklar';
$string['privacy:metadata:quizaccess_invigilator_rec:userid'] = 'Kaydı alınan kullanıcının ID bilgisi';
$string['privacy:metadata:quizaccess_invigilator_rec:recording'] = 'Yakalanan ekran karesinin bağlantısı';
$string['privacy:metadata:quizaccess_invigilator_rec:timecreated'] = 'Karenin yakalandığı zaman';
$string['setting:recordinginterval'] = 'Ekran görüntüleri arasındaki süre (saniye)';
$string['setting:recordinginterval_desc'] = 'İki ekran görüntüsü arasında geçen saniye. On saniye, ayrıntı ile depolama arasında iyi bir dengedir; süreyi yarıya indirmek kaplanan yeri iki katına çıkarır. İzin verilen aralık: 2 - 600.';
$string['setting:recordingquality'] = 'Ekran görüntüsü kalitesi';
$string['setting:recordingquality_desc'] = 'Her görüntünün JPEG kalitesi, 10 ile 100 arasında. 60 civarı ekrandaki yazıları okunur tutarken boyutu küçük bırakır; 85 üzerinde dosyalar gözle görülür bir kazanç olmadan hızla büyür.';
$string['recordingframes'] = 'Kare sayısı';
$string['playingframe'] = 'Kare {$a->number} / {$a->total} ({$a->time})';
$string['player:play'] = 'Oynat';
$string['player:pause'] = 'Duraklat';
$string['player:previous'] = 'Önceki kare';
$string['player:next'] = 'Sonraki kare';
$string['player:speed'] = 'Hız';
$string['player:fps'] = 'saniyede {$a} kare';

// Sınav öncesi adımlar: önce ekran paylaşımı, sonra onay.
$string['youmustsharescreen'] = 'Sınava başlamadan önce tüm ekranınızı paylaşmanız gerekir.';
$string['sharescreenfirst'] = 'Önce ekranınızı paylaşın, ardından bu kutuyu işaretleyin.';
$string['setting:captureheading'] = 'Ekran görüntüsü yakalama';
$string['setting:captureheading_desc'] = 'Öğrenci sınavı çözerken paylaşılan ekran birkaç saniyede bir görüntü olarak kaydedilir. Rapor, bir denemenin görüntülerini sırayla oynatarak sınavın tamamını hızlandırılmış biçimde izletir. Kaplanan yeri belirleyen iki değer aşağıdaki aralık ve kalitedir.';
$string['setting:storageheading'] = 'Depolama';
$string['setting:storageheading_desc'] = 'Yakalanan görüntülerin ne kadar saklanacağı ve bir görüntünün ulaşabileceği boyut sınırı.';
$string['error:outofrange'] = '{$a->min} ile {$a->max} arasında bir tam sayı girin.';
