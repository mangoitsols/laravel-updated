<!-- Streaming Link Modal -->
<div class="modal fade" id="streamingLinkModal" tabindex="-1" role="dialog" aria-labelledby="addStreamingLinkModal"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        @include('channels.links.create')
    </div>
</div>

<!-- Streaming Link Edit Modal -->
<div class="modal fade" id="editStreamingLinkModal" tabindex="-1" role="dialog"
    aria-labelledby="editStreamingLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        @include('channels.links.edit')
    </div>
</div>

<!-- Streaming Link Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    @include('channels.links.delete-links')
</div>
