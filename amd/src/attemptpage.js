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
 * Runs in the quiz window and watches the invigilator window that opened it.
 *
 * @module     quizaccess_invigilator/attemptpage
 * @copyright  2021 Brain Station 23
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props) {
                var quizurl = props.quizurl;

                /**
                 * Tell the invigilator window the attempt is over, so it can upload the
                 * segment it is recording instead of losing it.
                 */
                function notifyAttemptFinished() {
                    if (typeof window.opener != 'undefined' && window.opener !== null && !window.opener.closed) {
                        window.opener.postMessage({invigilator: 'attemptfinished'}, window.location.origin);
                    }
                }

                function CloseOnParentClose() {
                    // 1. Ana pencere kapandı mı kontrolü
                    if (typeof window.opener != 'undefined' && window.opener !== null) {
                        if (window.opener.closed) {
                            console.log('Parent window closed - closing quiz');
                            window.close();
                            return;
                        }

                        // 2. EKRAN PAYLAŞIMI KONTROLÜ
                        // Ana penceredeki ekran paylaşım durumu '0' (paylaşım yok) ise sınavı kapat
                        var shareState = window.opener.document.getElementById('invigilator_share_state');
                        if (shareState && shareState.value === '0') {
                            alert("Ekran paylaşımı durdurulduğu için sınav sonlandırılıyor.");
                            window.close(); // Sınav penceresini kapatır
                            window.opener.location.href = quizurl; // Ana pencereyi sınav girişine yönlendirir
                        }
                    }
                }

                $(window).ready(function() {
                    // Her 1 saniyede bir kontrol et
                    setInterval(CloseOnParentClose, 1000);

                    // Sınav gönderildiyse (özet/inceleme sayfası) kaydı sonlandır.
                    if (/\/(review|summary)\.php/.test(window.location.pathname)) {
                        notifyAttemptFinished();
                    }
                });
                return true;
            },
            init: function() {
                return true;
            }
        };
    });
