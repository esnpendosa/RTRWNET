@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                © <script>
                document.write(new Date().getFullYear())
                </script>, Muhammad As'ad Muhibbin Akbar - <a href="https://umg.ac.id" target="_blank" class="footer-link">Teknik Informatika UMG</a>
            </div>
            <div class="d-none d-lg-inline-block">
                <span class="footer-link me-4">Skripsi - Manajemen Jaringan WiFi</span>
            </div>
        </div>
    </div>
</footer>
<!--/ Footer-->