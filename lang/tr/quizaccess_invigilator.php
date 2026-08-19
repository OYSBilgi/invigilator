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
$string['setting:screenshotdelay'] = 'Ekran görüntüleri arasındaki süre (saniye)';
$string['setting:screenshotdelay_desc'] = 'Her ekran görüntüsü arasında beklenecek saniye sayısı.';
$string['setting:screenshotwidth'] = 'Ekran görüntüsü genişliği (piksel)';
$string['setting:screenshotwidth_desc'] = 'Kaydedilecek ekran görüntüsünün genişliği. Yükseklik en-boy oranına göre hesaplanır.';
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
$string['screenhtml'] = '<span><video id="invigilator-video-screen" width="320" height="240" autoplay></video></span><canvas id="invigilator-canvas-screen" style="display:none;"></canvas><img id="invigilator-photo-screen" alt="Görüntü bu alanda gösterilecek." style="display:none;"/><span class="invigilator-output-screen" style="display:none;"></span><span id="invigilator-log-screen" style="display:none;"></span><span id="invigilator-recording-status" class="invigilator-recording-status"></span>';
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
$string['setting:recordingheading'] = 'Ekran kaydı';
$string['setting:recordingheading_desc'] = 'Belirli aralıklarla alınan ekran görüntülerine ek olarak, paylaşılan ekran sınav boyunca örneklenebilir: birkaç saniyede bir sıkıştırılmış görüntü alınır ve raporda hızlandırılmış (timelapse) biçimde izlenir. Bu yöntem videonun kapladığı yerin küçük bir kısmını kullanır.';
$string['setting:enablerecording'] = 'Ekran yakalamayı etkinleştir';
$string['setting:enablerecording_desc'] = 'Etkinleştirilirse, paylaşılan ekran sınav boyunca belirli aralıklarla görüntülenir ve kareler raporda hızlandırılmış biçimde oynatılır.';
$string['setting:recordingwidth'] = 'Kare genişliği (piksel)';
$string['setting:recordingwidth_desc'] = 'Her kare yüklenmeden önce en fazla bu genişliğe küçültülür. Küçük değerler dosya boyutunu belirgin şekilde azaltır.';
$string['setting:recordingmaxsize'] = 'En büyük kare boyutu (MB)';
$string['setting:recordingmaxsize_desc'] = 'Bu boyutu aşan kareler reddedilir. Yükleme base64 ile kodlandığı için görüntüden yaklaşık üçte bir oranında büyür; bu değeri sunucunuzun PHP post_max_size ayarının altında tutun.';
$string['setting:recordingretention'] = 'Yakalanan görüntülerin saklanma süresi (gün)';
$string['setting:recordingretention_desc'] = 'Bu süreden eski kareler zamanlanmış görev tarafından silinir. Elle silinene kadar saklamak için 0 girin.';

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
$string['setting:recordinginterval'] = 'Yakalama aralığı (saniye)';
$string['setting:recordinginterval_desc'] = 'İki kare arasında geçen süre. On saniye, ayrıntı ile depolama arasında iyi bir dengedir.';
$string['setting:recordingquality'] = 'Görüntü kalitesi (1-100)';
$string['setting:recordingquality_desc'] = 'Her karenin JPEG kalitesi. 60 civarı, ekrandaki yazıları okunur tutarken boyutu düşük tutar.';
$string['recordingframes'] = 'Kare sayısı';
$string['playingframe'] = 'Kare {$a->number} / {$a->total} ({$a->time})';
$string['player:play'] = 'Oynat';
$string['player:pause'] = 'Duraklat';
$string['player:previous'] = 'Önceki kare';
$string['player:next'] = 'Sonraki kare';
$string['player:speed'] = 'Hız';
$string['player:fps'] = 'saniyede {$a} kare';
