<script>
(function() {
    stalkerUrl = document.location.protocol + "//<stalker_host>/sites/<site_id>";
    var stalker = document.createElement("script");
    stalker.type = "text/javascript";
    stalker.async = true;
    stalker.src = stalkerUrl + "/tracking-code";
    $(document.body).append(stalker)
}())
</script>