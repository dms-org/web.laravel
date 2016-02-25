<div class="dropzone-container"
     data-name="{{ $name }}"
     data-field-validation-for="{{ $name }}[action], {{ $name }}[file]"
     @if ($required) data-required="1" @endif
     data-upload-temp-file-url="{{ route('dms::file.upload') }}"
     @if($maxFileSize ?? false) data-max-size="{{ $maxFileSize }}" @endif
     @if($extensions ?? false) data-allowed-extensions="{{ json_encode($extensions) }}" @endif
     data-file="{{ json_encode($existingFile) }}"
     data-tempfile-key-prefix="{{ \Dms\Web\Laravel\Action\InputTransformer\TempUploadedFileToUploadedFileTransformer::TEMP_FILES_KEY }}"
>
    <div class="dms-dropzone"></div>
</div>