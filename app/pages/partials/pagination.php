<?php
$paginator = new Paginator($pageNo, $per, $total);
$pageCount = $paginator->pages();
if ($pageCount <= 1) {
    return;
}
$startPage = max(1, $pageNo - 2);
$endPage = min($pageCount, $pageNo + 2);
$pageUrl = static function (int $target): string {
    $query = $_GET;
    $query['page'] = $target;
    return '?' . http_build_query($query);
};
?>
<nav aria-label="Paginação" class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $pageNo <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($pageUrl(max(1, $pageNo - 1))) ?>">Anterior</a>
        </li>
        <?php for ($number = $startPage; $number <= $endPage; $number++): ?>
            <li class="page-item <?= $number === $pageNo ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($pageUrl($number)) ?>"><?= $number ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pageNo >= $pageCount ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($pageUrl(min($pageCount, $pageNo + 1))) ?>">Próxima</a>
        </li>
    </ul>
</nav>
