<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Festival Ticket Scanner</h1>

        <?php if (isset($status)): ?>
            <?php
            $bgClass = 'bg-gray-100 border-gray-400 text-gray-800';
            $icon = '❌';
            if ($status === 'success') {
                $bgClass = 'bg-green-100 border-green-400 text-green-800';
                $icon = '✅';
            } elseif ($status === 'warning') {
                $bgClass = 'bg-red-100 border-red-400 text-red-800';
                $icon = '⚠️';
            }
            ?>

            <div class="border-l-4 p-6 rounded mb-6 text-xl font-medium <?= $bgClass ?>">
                <div class="mb-2 text-4xl"><?= $icon ?></div>
                <div class="mb-4"><?= htmlspecialchars($message ?? '') ?></div>

                <?php if (isset($eventName)): ?>
                    <div class="mt-4 pt-4 border-t border-opacity-30 border-current bg-white bg-opacity-30 rounded px-2 py-3">
                        <p class="text-lg font-bold mb-1"><?= htmlspecialchars($eventName) ?></p>
                        <?php if (isset($eventTime)): ?>
                            <p class="text-sm">Time: <?= htmlspecialchars($eventTime) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <a href="/scanner"
                class="inline-block w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded shadow transition-colors text-lg">
                Scan Next Ticket
            </a>
        <?php else: ?>
            <div id="reader" class="mb-4 overflow-hidden outline-none"></div>

            <form id="scanForm" method="POST" action="/scanner/process">
                <?php if (isset($csrfToken)): ?>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <input type="hidden" name="qr_hash" id="qrInput">
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    let isProcessing = false;

                    function onScanSuccess(decodedText, decodedResult) {
                        if (isProcessing) return;
                        isProcessing = true;

                        const scanner = document.getElementById('reader');
                        if (scanner) scanner.style.display = 'none'; // Stop scanning loop visually

                        try {
                            html5QrcodeScanner.clear(); // Stop the scanner completely to prevent multiple fires
                        } catch (e) {
                            console.error(e);
                        }

                        document.getElementById('qrInput').value = decodedText;
                        document.getElementById('scanForm').submit();
                    }

                    function onScanFailure(error) {
                        // Keep scanning
                    }

                    const html5QrcodeScanner = new Html5QrcodeScanner(
                        "reader", {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        /* verbose= */
                        false
                    );
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                });
            </script>
        <?php endif; ?>
    </div>
</body>

</html>