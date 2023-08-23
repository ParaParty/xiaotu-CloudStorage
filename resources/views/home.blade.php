<!-- home.blade.php -->
@extends('layout.layout')

@section('title','个人主页')

@section('head')
    <script src="{{ asset('assets/js/sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/iconfont.js') }}"></script>
    <script src="{{ asset('assets/js/api.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>

        .file-box {
            height: 600px !important;
        }

        .file-nav {
            margin-bottom: -15px;
        }

        .file-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            overflow: auto;
        }

        .file-list li {
            height: 150px;
            width: 20%;
            list-style: none;
            box-sizing: border-box;
            position: relative;
        }

        .file-item {
            text-align: center;
        }

        .file-name {
            user-select: none;
        }

        .file-icon {
            position: relative;
            display: inline-block;
        }

        .file-icon .icon {
            width: 100px;
            height: 100px;
            transition: transform 0.3s ease-in-out;
        }

        .file-icon:hover .icon {
            transform: scale(1.2); /* 鼠标聚焦时放大1.2倍 */
            filter: brightness(90%);
        }

        .file-icon:hover .file-buttons {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .file-buttons {
            display: none;
            transition: transform 0.3s ease-in-out;
        }

        /* 弹出弹窗容器 */
        .file-info {
        }

        /* 弹出弹窗内容样式 */
        .file-info p {
            margin: 0;
            text-align: left;
        }

        .file-menu {
            width: 100%;
        }

        .file-menu > button {
            /*width: 20px !important; !* 设置按钮的宽度为100像素 *!*/
            height: 33px; /* 设置按钮的高度为30像素 */
            padding: 0;

        }

        .file-menu > button i {
            margin-left: 2px;
            margin-right: 7px;
        }

        .filebox-hr {
            margin: 7px;
            border: none;
            border-top: 1px solid #ccc;
        }

        .upload-progress {
            margin: 0 12px;
        }
    </style>



@endsection

@section('main')
    {{--    <div>欢迎{{ Auth::user()->name }}!</div>--}}
    <div class="container-fluid">
        <div class="row">
            <div class="file-sidebar flex-shrink-0 p-3 col-12 col-md-4 col-lg-3">
                <a href="/"
                   class="d-flex align-items-center justify-content-center pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom">
                    <span class="fs-2 fw-semibold">XuriCloud</span>
                </a>
                <ul class="list-unstyled ps-0">
                    <li class="mb-1">
                        <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0"
                                data-bs-toggle="collapse" data-bs-target="#orders-collapse" aria-expanded="true">默认分类
                        </button>
                        <div class="collapse show" id="orders-collapse">
                            <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                                <li><a href="#"
                                       class="link-body-emphasis d-inline-flex text-decoration-none rounded">视频</a></li>
                                <li><a href="#"
                                       class="link-body-emphasis d-inline-flex text-decoration-none rounded">音频</a></li>
                                <li><a href="#"
                                       class="link-body-emphasis d-inline-flex text-decoration-none rounded">图片</a></li>
                                <li><a href="#"
                                       class="link-body-emphasis d-inline-flex text-decoration-none rounded">压缩文件</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="border-top my-3"></li>
                </ul>
            </div>

            <div class="file-box card col-12 col-md-8 col-lg-9 d-flex p-3">

                <div style="width: 135px;">
                    <div class="btn-group file-menu" role="group" aria-label="文件功能菜单">
                        <button class="file-upload btn btn-primary"><i class="bi bi-upload"></i>上传</button>
                        <input class="file-input" type="file" style="display:none;">
                        <button class="btn btn-primary search"><i class="bi bi-search"></i>搜索</button>

                    </div>


                    <div class="input-group input" style="display: none; position: relative;">
                        <label>
                            <input class="form-control" type="text" style="position: absolute;" placeholder="按下回车以检索">
                        </label>
                    </div>
                </div>

                <hr class="filebox-hr">
                <nav aria-label="文件路径导航" class="file-nav">
                    <ol class="breadcrumb">
                    </ol>
                </nav>
                <hr class="filebox-hr">
                <ul class="file-list">


                </ul>

            </div>
        </div>

    </div>

@endsection

@section('body-end')
    <script type="module">
        //初始化
        let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        const msg_toast = bootstrap.Toast.getOrCreateInstance($('.msg-toast')[0]);

        let userData = {};
        let workingDirectory = '/';

        function makeTooltipHtml(param) {
            let html = `<div class='file-info'>`;
            $.each(param, function (key, value) {
                html += `<p>${key}: ${value}</p>`;
            });
            html += '</div>';
            return html;
        }

        function getFileTypeIcon(fileName) {
            const extension = fileName.split('.').pop().toLowerCase();

            // 视频类
            if (['mp4', 'avi', 'mkv', 'mov', 'flv', 'webm'].includes(extension)) {
                return '#icon-shipin';
            }

            // 音频类
            if (['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'].includes(extension)) {
                return '#icon-music';
            }

            // 图片类
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'].includes(extension)) {
                if (extension === 'gif') {
                    return '#icon-Gif';
                }
                return '#icon-tupian';
            }

            // 压缩文件
            if (['zip', 'rar', '7z', 'tar', 'gz', 'xz'].includes(extension)) {
                return '#icon-yasuobao';
            }

            // 文档类
            if (['pdf'].includes(extension)) {
                return '#icon-Pdf';
            }
            if (['xlsx', 'xls', 'csv'].includes(extension)) {
                return '#icon-Excel';
            }
            if (['docx', 'doc', 'odt', 'odp'].includes(extension)) {
                return '#icon-Word';
            }
            if (['ppt', 'pptx'].includes(extension)) {
                return '#icon-PPT';
            }

            // 代码文件
            if (['js', 'py', 'java', 'c', 'cpp', 'html', 'css', 'php', 'rb', 'swift', 'pl', 'lua', 'ts', 'scss', 'json', 'xml', 'tsv', 'yaml'].includes(extension)) {
                return '#icon-chengxu';
            }

            // 文本文件
            if (['log', 'md', 'markdown', 'txt'].includes(extension)) {
                return '#icon-txt';
            }

            // 可执行文件
            if (['exe', 'bat', 'sh', 'jar', 'msi'].includes(extension)) {
                return '#icon-Exe';
            }

            // 其余文件
            return '#icon-weizhiwenjian';
        }

        function refreshFileBox(newDir) {
            window.isFilelistLoading = true;
            workingDirectory = newDir;
            api('{{ route('api.storage.get-path') }}', {
                path: workingDirectory
            }, (data) => {
                console.log(data.data);
                $('.breadcrumb')
                    .empty();
                let pathParts = ('/' + userData.name + workingDirectory).split('/');
                $.each(pathParts, function (index, part) {
                    $('.breadcrumb').append(
                        `<li class="breadcrumb-item"><a href="#">${part}</a></li>`
                    );
                });
                $('.breadcrumb li')
                    .eq(-2)
                    .addClass('active')
                    .attr("aria-current", "page")
                    .empty()
                    .text(pathParts[pathParts.length - 2]);

                $('.file-list').empty();
                // 文件夹渲染
                $.each(data.data.folders, function (index, folder) {
                    let tooltipHtml = makeTooltipHtml({
                        "文件夹名": folder.name,
                        "备注": folder.note,
                        "创建时间": folder.created_at,
                    })
                    let item = `
                        <li>
                            <div class="file-item">
                                <div class="file-icon" data-filetype="folder" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" data-bs-title="${tooltipHtml}">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#icon-File"></use>
                                    </svg>
                                </div>
                                <div class="file-name">${folder.name}</div>
                            </div>
                        </li>`;
                    $('.file-list').append(item);
                });

                // 文件渲染
                $.each(data.data.files, function (index, file) {
                    let tooltipHtml = makeTooltipHtml({
                        "文件名": file.name,
                        "备注": file.note,
                        "创建时间": file.created_at,
                    })
                    let item = `
                        <li>
                            <div class="file-item">
                                <div class="file-icon" data-filetype="file" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" data-bs-title="${tooltipHtml}">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="${getFileTypeIcon(file.name)}"></use>
                                    </svg>
                                </div>
                                <div class="file-name">${file.name}</div>
                            </div>
                        </li>`;
                    $('.file-list').append(item);
                });

                for (let i = $('.file-list li').length % 5; i > 0; i--) {
                    $('.file-list').append('<li/>')
                }
                tooltipTriggerList = document.querySelectorAll('.file-icon');
                tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
                window.isFilelistLoading = false;
            });
        }

        function setMsgToast(msg, title = 'Message', ttl = 2000) {
            let toast_dom = $(`
                <div class="msg-toast-div toast-container position-fixed bottom-0 end-0 p-3">
                    <div class="toast msg-toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header my-1">
                            <strong class="me-auto"><i class="bi bi-chat-dots-fill" style="color: #0d6efd;margin-right: 8px;"></i>${title}</strong>
                            <small>just</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body mt-1 mb-2 mx-2">
                            ${msg}
                        </div>
                    </div>
                </div>
            `);
            $('.toast-box').append(toast_dom);
            let toast = bootstrap.Toast.getOrCreateInstance(toast_dom.find('.msg-toast')[0], {delay: ttl});
            toast.show();
            toast_dom.css('user-select', 'none');
            setTimeout((toast_dom) => {
                toast_dom.remove();
            }, ttl, toast_dom);

        }

        api('{{ route('api.user.user') }}', {}, (data) => {
            userData = data;
            //workingDirectory += userData.name + '/';
            refreshFileBox(workingDirectory);
        });

        $('.file-list').on('dblclick', '.file-icon', function () {
            //bootstrap.Tooltip.getInstance(this).dispose();
            $.each(tooltipList, function (key, value) {
                value.hide();
            });
            if ($(this).attr('data-filetype') === 'folder') {
                refreshFileBox(workingDirectory + $(this).siblings('.file-name').text() + '/');
                return;
            }
            //文件
            setMsgToast('The file will be downloaded.', 'Message');
            let filename = $(this).next('.file-name').text();
            api('{{ route('api.storage.download-file-get') }}', {
                'path': workingDirectory,
                'filename': filename
            }, (data) => {
                if (data.code === 200) {
                    window.location.href = data.data.download;
                }
            });


        });

        $('.breadcrumb').on('click', 'li a', function () {
            let clickedA = $(this);
            let breadcrumbText = $(this).text() + '/';

            // 遍历当前 a 标签之前的所有 a 标签
            $(this).parent().prevAll('li').each(function () {
                let prevA = $(this).find('a');
                breadcrumbText = prevA.text() + "/" + breadcrumbText;
                // 如果当前 a 标签是被点击的 a 标签，停止遍历
                if (prevA.is(clickedA)) {
                    return false;
                }
            });
            let dir = '/' + breadcrumbText.split('/').splice(2).join('/');
            refreshFileBox(dir);
        });

        $('.search').mouseenter(function () {
            $('.input').css({
                'display': 'block'
            }).find('input').focus();
        }).mouseleave(function () {
            $('.input').css('display', 'none');
        }).click(() => {
            api('{{ route('api.storage.upload-file') }}', {
                id: 164
            }, (data) => {
                console.log(data);
            });
        });

        $('.file-upload').click(() => {
            $('.file-input').click();
        });

        $('.file-input').click(() => {

        }).change(function (event) {
            window.upload_file = event.target.files[0];
            this.value = '';
            new bootstrap.Modal(upload_model).show();

        });

        let upload_model = $('.upload-model');
        let upload_status_model = $('.upload-status-model');
        upload_model[0].addEventListener('shown.bs.modal', function (event) {
            $(this).find('.file-name').focus()
            $(this).find('.file-name')[0].setSelectionRange(0, upload_file.name.lastIndexOf('.'));
        });
        upload_model[0].addEventListener('show.bs.modal', function (event) {
            $(this).find('.file-name').val(upload_file.name);
        });

        $('.upload-confirm').click(function () {
            //new bootstrap.Modal(upload_model).hide();  //no response
            window.upload_status_model_func = new bootstrap.Modal('.upload-status-model');
            window.upload_status_model_func.show();
        });

        upload_status_model[0].addEventListener('hide.bs.modal', function (event) {
            //暂时方法，注意重构
            let status = upload_status_model.find('.upload-progress').attr('aria-valuenow');
            refreshFileBox(workingDirectory);

            if (status !== '100') {
                event.preventDefault();
                //upload_status_model_close_toast.show();
                setMsgToast('Your typing is close.');
            }
        });
        upload_status_model[0].addEventListener('show.bs.modal', function (event) {
            //暂时方法，注意重构
            upload_status_model.find('.progress-bar').css('width', '0%').text('');
        });
        upload_status_model[0].addEventListener('shown.bs.modal', function (event) {
            let Progress = function (progress) {
                this.progress = progress;
                this.progress_bar = this.progress.find('div.progress-bar').first();
                this.getProgress = function () {
                    return Math.round((this.progress_bar.width() / this.progress.width()) * 100);
                };
                this.setProgress = function (width) {
                    this.progress_bar.css('width', width + '%');
                    this.progress.attr("aria-valuenow", width);
                };
                this.addProgress = function (width) {
                    let nowProgress = Math.round((this.progress_bar.width() / this.progress.width()) * 100);
                    this.progress_bar.css('width', width + nowProgress + '%');
                    this.progress.attr("aria-valuenow", nowProgress + width);
                };
                this.setTitle = function (title) {
                    this.progress_bar.text(title);
                };
            };
            let progress = new Progress(upload_status_model.find('.upload-progress'));
            progress.setProgress(0);
            progress.setTitle('正在创建文件...');
            api('{{ route('api.storage.make-file') }}', {
                name: upload_model.find('.file-name').val(),
                path: workingDirectory,
                note: upload_model.find('.file-note').val()
            }, (data) => {
                if (data.code === 200) {
                    //初始进度 10
                    let p_start = 10;
                    let p_end = 10;
                    progress.setProgress(p_start);
                    progress.setTitle('正在上传文件...');
                    let info = upload_status_model.find('.upload-info');
                    let file_id = data.data.id;
                    let chunkSize = 1024 * 1024 * 2; // 假设每个分片大小为 1MB
                    let uploaded_chunks = 1;
                    let total_chunks = Math.ceil(upload_file.size / (chunkSize));

                    let queue = [];
                    setTimeout(async () => {
                        do {
                            let currentChunk = upload_file.slice((uploaded_chunks - 1) * chunkSize, uploaded_chunks * chunkSize);
                            let reader = new FileReader();
                            let wordArray;

                            function loadFile(file) {
                                return new Promise((resolve, reject) => {
                                    reader.onload = () => {
                                        resolve(reader.result)
                                    }
                                    reader.onerror = reject;
                                    reader.readAsArrayBuffer(file);
                                });
                            }

                            let readerFile = await loadFile(currentChunk);
                            wordArray = CryptoJS.lib.WordArray.create(readerFile);
                            let hash = await CryptoJS.SHA256(wordArray).toString();
                            queue.push({
                                'uploaded_chunks': uploaded_chunks,
                                'hash': hash,
                                'file': currentChunk
                            });
                            uploaded_chunks++;
                        } while (uploaded_chunks <= total_chunks);
                    }, 0);

                    let uploaded_sum = 0;
                    let upload = async () => {
                        let i = queue.length;
                        if (i === 0) {
                            if (uploaded_sum !== total_chunks) {
                                setTimeout(() => {
                                    upload()
                                }, 1000);
                                return;
                            }

                            progress.setTitle('正在合成文件...');
                            api('{{ route('api.storage.upload-file') }}', {
                                id: file_id
                            }, (data) => {
                                //分片文件合并完毕，上传成功！
                                progress.setProgress(100);
                                setTimeout(() => {
                                    window.upload_status_model_func.hide();
                                }, 800)
                            });
                            return;
                        }

                        let fileData = queue[i - 1];
                        queue.splice(i - 1, 1);
                        let uploadForm = new FormData();
                        uploadForm.append('id', file_id);
                        uploadForm.append('total_chunks', total_chunks);
                        uploadForm.append('uploaded_chunks', fileData.uploaded_chunks);
                        uploadForm.append('file', fileData.file, 'file');
                        uploadForm.append('hash', fileData.hash);
                        try {
                            let response = await fetch('{{ route('api.storage.upload-file') }}', {
                                method: "POST",
                                body: uploadForm,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                                }
                            });
                            let data = await response.json();
                            uploaded_sum++;
                            // 80 为满进度 100 减去 初始进度 10 得出的结果 减去预留合并文件 10 的结果
                            let p = Math.round(uploaded_sum / total_chunks * (100 - p_start - p_end));
                            progress.setProgress(p + p_start);

                        } catch (error) {
                            queue.push(fileData);
                        }

                        setTimeout(() => {
                            upload()
                        }, 100);
                    };

                    //setInterval(()=>console.log(queue),1500);
                    upload();

                }
            });
        });

        $('.input').on('keydown', function (event) {
            if (event.keyCode === 13) {
                let text = $('.input').find('input').val();
                if (text !== '') {
                    window.open('https://www.baidu.com/s?wd=' + text, '_blank');
                }
            }
        });
    </script>

    <div><!-- Modal -->
        <div class="modal fade upload-model" data-bs-backdrop="static" tabindex="-1"
             aria-labelledby="upload-model" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">上传文件</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">文件名:</label>
                                <input type="text" class="form-control file-name">
                            </div>
                            <div class="mb-3">
                                <label for="message-text" class="col-form-label">备注:</label>
                                <textarea class="form-control file-note"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消
                        </button>
                        <button type="button" class="btn btn-primary upload-confirm" data-bs-dismiss="modal">上传</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade upload-status-model" data-bs-backdrop="static" tabindex="-1"
             aria-labelledby="upload-status-model" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">正在上传</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="upload-progress progress" role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                 aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                     style="width: 0%">上传中...
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="upload-info">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-box d-flex flex-column">


    </div>
    @include('layout.color-mode')
@endsection





