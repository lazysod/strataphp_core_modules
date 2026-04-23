<?php
namespace App\Modules\GoogleAnalytics;

/**
 * GoogleAnalytics
 *
 * Outputs the Google Analytics script if a Measurement ID is set in config.
 */
class GoogleAnalytics
{
    private $measurementId;

    public function __construct($config = [])
    {
        $this->measurementId = $config['measurement_id'] ?? '';
    }

    /**
     * Render the Google Analytics script tag
     * @return string
     */
    public function renderScript()
    {
        try {
            if (!$this->measurementId) {
                return '<!-- Google Analytics Measurement ID not set -->';
            }
            return <<<HTML
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$this->measurementId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$this->measurementId}');
</script>
<!-- End Google Analytics -->
HTML;
        } catch (\Throwable $e) {
            return '<!-- Google Analytics error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }
    }
}
