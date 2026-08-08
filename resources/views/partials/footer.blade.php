<footer class="py-4 px-4 text-center">
    <div class="text-muted fs-xs">
        &copy; {{ date('Y') }} <span class="fw-600">{{ setting('company.name', app_name()) }}</span> — All rights reserved.
        <span class="mx-1">·</span>
        <span>v{{ app_version() }}</span>
    </div>
</footer>
