define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props) {
                var quizurl = props.quizurl;

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
                });
                return true;
            },
            init: function() {
                                return true;
            }
        };
    });