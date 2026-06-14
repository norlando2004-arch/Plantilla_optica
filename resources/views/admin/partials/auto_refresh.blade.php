@php
    $autoRefreshEnabled = isset($enabled) ? (bool) $enabled : request()->boolean('auto_refresh');
    $mode = isset($mode) ? (string) $mode : 'interval';
    $intervalMs = isset($intervalMs) ? max(3000, (int) $intervalMs) : 10000;
    $checkUrl = isset($checkUrl) ? (string) $checkUrl : '';
    $watchToken = isset($watchToken) ? (string) $watchToken : '';
@endphp

@if($autoRefreshEnabled)
    @if($mode === 'on-change' && $checkUrl !== '' && $watchToken !== '')
        <script>
            (function () {
                var watchToken = @json($watchToken);
                var checkUrl = @json($checkUrl);
                var intervalMs = {{ $intervalMs }};
                var timerId = null;

                var checkForChanges = function () {
                    fetch(checkUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Status ' + response.status);
                            }
                            return response.json();
                        })
                        .then(function (payload) {
                            var nextToken = payload && typeof payload.token !== 'undefined'
                                ? String(payload.token)
                                : '';

                            if (nextToken !== '' && nextToken !== watchToken) {
                                if (timerId) {
                                    clearInterval(timerId);
                                }
                                window.location.reload();
                            }
                        })
                        .catch(function () {
                            // Ignorar errores de red puntuales para no interrumpir la vista.
                        });
                };

                timerId = setInterval(checkForChanges, intervalMs);
            })();
        </script>
    @else
        <script>
            // Recarga automática clásica cada N segundos.
            setInterval(function () {
                window.location.reload();
            }, {{ $intervalMs }});
        </script>
    @endif
@endif
