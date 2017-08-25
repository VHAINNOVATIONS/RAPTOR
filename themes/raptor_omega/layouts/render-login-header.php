<div style="display: none;" hidden>
    <!-- PHP variables being passed to JavaScript -->
    <?php global $base_url ?>
    <script>
        Drupal.pageData.baseURL = <?php echo json_encode($base_url) ?>;
    </script>
</div>
