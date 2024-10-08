<?php

try {
    require __DIR__ . '/../app/bootstrap.php';

    /** @var \Vch\Framework\Model\Http $app */
    $app = \Vch\Framework\Model\Invoker::getInstance()->get(\Vch\Framework\Model\Http::class);
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 10px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:10px 0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$app->run();
