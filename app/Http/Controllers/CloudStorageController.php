<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileUploadInfo;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CloudStorageController extends Controller
{
    public function getPath(Request $request): array
    {
        $path = $request->input('path');
        str_contains($path, '/') || $path = '/' . $path;
        $data = UserFile::where('owner_id', Auth::user()->getAuthIdentifier())
            ->where('path', $path);
        $content = [
            'folders' => [],
            'files' => []
        ];
        $data->each(static function ($row) use (&$content) {
            if ($row->type === 'folder') {
                $content['folders'][] = [
                    'name' => $row->filename,
                    'note' => $row->note ?? '',
                    'created_at' => date('Y年m月d日 H:i:s', strtotime($row->created_at))
                ];
            } elseif ($row->type === 'file') {
                $content['files'][] = [
                    'name' => $row->filename,
                    'note' => $row->note ?? '',
                    'created_at' => date('Y年m月d日 H:i:s', strtotime($row->created_at))
                ];
            }
        });

        return $this->makeReturn(200, 'ok', $content);
        //This is cos/oss section.
        //This is location section.
    }

    public function makeFile(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'hash' => 'nullable|string',
            'path' => 'required|string',
            'note' => 'nullable|string|max:180',
        ]);

        if ($validator->fails()) {
            // 校验失败，返回错误响应
            return $this->makeReturn(-100, '参数缺省', [
                'error' => '参数校验失败',
                'errors' => $validator->errors(),
            ]);
        }
        $path = $request->input('path');
        if ($path !== '/' && !UserFile::where('owner_id', Auth::user()->getAuthIdentifier())->where('path', dirname($path) . '/')->where('type', 'folder')->where('filename', basename($path))->exists()) {
            return $this->makeReturn(112, '文件夹不存在');
        }
        //hash
        $name = $request->input('name');
        if (UserFile::where('owner_id', Auth::user()->getAuthIdentifier())->where('path', $path)->where('filename', $name)->exists()) {
            return $this->makeReturn(103, '文件已存在');
        }
        $hash = $request->input('hash');
        $note = $request->input('note');
        $file = File::where('hash', $hash);
        $userFile = new UserFile();
        $userFile->owner_id = Auth::user()->getAuthIdentifier();
        $userFile->filename = $name;
        $userFile->path = $path;
        $userFile->type = 'file';
        $userFile->note = $note;
        if ($file->exists()) {
            $userFile->hash = $hash;
            $userFile->save();
            return $this->makeReturn(200, 'ok', [
                'type' => 1,//闪传
                'name' => $name,
                'hash' => $hash,
                'path' => $path
            ]);
        }
        $userFile->save();
        return $this->makeReturn(200, 'ok', [
            'type' => 0,//待上传
            'id' => $userFile->id,
            'name' => $name,
            'hash' => $hash,
            'path' => $path
        ]);
    }

    public function uploadFile(Request $request): array
    {
        if ($request->has('file')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'hash' => 'required|string', // 假设hash值以字符串形式传递
                'total_chunks' => 'required|integer',
                'uploaded_chunks' => 'required|integer',
            ]);

            if ($validator->fails()) {
                // 校验失败，返回错误响应
                return $this->makeReturn(-100, '参数缺省', [
                    'error' => '参数校验失败',
                    'errors' => $validator->errors(),
                ]);
            }
            //分片文件上传
            $file = $request->file('file');
            $hash = $request->input('hash');
            if (hash('sha256', $file->getContent()) !== $hash) {
                return $this->makeReturn(101, '哈希错误');
            }
            $total_chunks = $request->input('total_chunks');
            $uploaded_chunks = $request->input('uploaded_chunks');
            if ($total_chunks < $uploaded_chunks) {
                return $this->makeReturn(101, '当前分片大于总分片数');
            }
            $id = $request->input('id');
            $path = $file->storeAs('file_uploads', $hash);
            $fileData = new FileUploadInfo();
            $fileData->owner_id = Auth::user()->getAuthIdentifier();
            $fileData->file_id = $id;
            $fileData->file_path = $path;
            $fileData->file_hash = $hash;
            $fileData->total_chunks = $total_chunks;
            $fileData->uploaded_chunks = $uploaded_chunks;
            $fileData->save();

            return $this->makeReturn(200, '上传成功', [
                'id' => $id,
                'total_chunks' => $total_chunks,
                'uploaded_chunks' => $uploaded_chunks,
            ]);
        }

        $id = $request->input('id');
        if ($id !== null) {
            //分片文件合并
            $fileData = FileUploadInfo::where('owner_id', Auth::user()->getAuthIdentifier())
                ->where('file_id', $id);
            if (!$fileData->exists()) {
                return $this->makeReturn(101, '分片文件不存在');
            }
            if ($fileData->count() < $fileData->get()->first()->total_chunks) {
                return $this->makeReturn(101, '分片文件没有全部上传');
            }
            // 创建一个临时文件用于存储合并后的内容
            $tempFilePath_relative = 'tmp/upload/' . $id;
            Storage::put($tempFilePath_relative, '');
            $tempFilePath = Storage::path($tempFilePath_relative);
            $tempFile = fopen($tempFilePath, 'wb');
            $uploadedFiles = $fileData->orderBy('uploaded_chunks', 'asc')->get();
            foreach ($uploadedFiles as $value) {
                fwrite($tempFile, Storage::get($value->file_path));
                //删除分片数据库记录和分片文件
                Storage::delete($value->file_path);
                $value->delete();
            }
            // 关闭临时文件
            fclose($tempFile);
            // 生成唯一的目标文件名
            $hash = hash_file('sha256', $tempFilePath);
            // 检测文件是否存在
            $file = new File();
            if (!$file->where('hash', $hash)->exists()) {
                // 将临时文件移动到目标文件夹中
                Storage::move($tempFilePath_relative, 'files/' . $hash);
                //生成数据库记录
                $file->filename = $hash;
                $file->hash = $hash;
                $file->path = 'files/' . $hash;
                $file->save();
            }
            //链接到用户目录
            $userFile = UserFile::where('owner_id', Auth::user()->getAuthIdentifier())
                ->where('id', $id)
                ->first();
            $userFile->hash = $hash;
            $userFile->save();
            return $this->makeReturn(200, 'ok', [
                'filename' => $userFile->filename,
                'path' => $userFile->path
            ]);
        }

        return $this->makeReturn(-100, '参数缺省');
    }

    public function downloadFileGet(Request $request): array
    {
        if ($request->isMethod('get')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string'
            ]);
            if ($validator->fails()) {
                // 校验失败，返回错误响应
                return $this->makeReturn(-100, '参数缺省', [
                    'error' => '参数校验失败',
                    'errors' => $validator->errors(),
                ]);
            }
            $id = $request->query('id');

        }
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'path' => 'required|string',
        ]);
        if ($validator->fails()) {
            // 校验失败，返回错误响应
            return $this->makeReturn(-100, '参数缺省', [
                'error' => '参数校验失败',
                'errors' => $validator->errors(),
            ]);
        }
        $filename = $request->input('filename');
        $path = $request->input('path');
        $userFile = UserFile::where('owner_id', Auth::user()->getAuthIdentifier())->where('path', $path)->where('type', 'file')->where('filename', $filename)->whereNotNull('hash');
        if (!$userFile->exists()) {
            return $this->makeReturn(102, '文件不存在');
        }
        $down_id = Str::uuid();
        $filehash = $userFile->first()->hash;
        //文件过期时间
        $ttl = 60;
        Cache::put($down_id, $filehash, $ttl);
        return $this->makeReturn(200, 'ok', [
            'download' => route('api.storage.download-file', $down_id),
            'ttl' => $ttl
        ]);
    }

    public function downloadFile(Request $request, string $id): BinaryFileResponse|string
    {
        $hash = Cache::pull($id);
        $file = File::where('hash', $hash);
        if (!$file->exists()) {
            return '';
        }
        $path = $file->first()->path;
        $filename = UserFile::where('owner_id', Auth::user()->getAuthIdentifier())->where('hash', $hash)->first()->filename;
        return response()->download(Storage::path($path), $filename);
    }

    public function createFolder(Request $request): array
    {
        $folderName = $request->input('folderName');
        $path = $request->input('path');
        str_starts_with($path, '/') || $path = '/' . $path;
        str_ends_with($path, '/') || $path .= '/';

        $userStorage = UserFile::where('owner_id', Auth::user()->getAuthIdentifier());
        if ($path !== '/') {
            $existingParentFolder = $userStorage
                ->where('path', dirname($path) === '\\' ? '/' : dirname($path) . '/')
                ->where('type', 'folder')
                ->where('filename', basename($path))
                ->exists();
            if (!$existingParentFolder) {
                return $this->makeReturn(101, '父级目录不存在');
            }
        }
        $existingFolder = $userStorage
            ->where('path', $path)
            ->where('type', 'folder')
            ->where('filename', $folderName)
            ->exists();

        if ($existingFolder) {
            return $this->makeReturn(102, '该文件夹已存在');
        }

        // 生成文件夹
        $userFile = new UserFile();
        $userFile->owner_id = Auth::user()->getAuthIdentifier();
        $userFile->filename = $folderName;
        $userFile->path = $path;
        $userFile->type = 'folder';
        $userFile->save();

        return $this->makeReturn(200, 'ok');
    }

    public function deleteFolder(Request $request): array
    {
        $folderName = $request->input('folderName');
        $path = $request->input('path');
        str_contains($path, '/') || $path = '/' . $path;
        $aimFolder = UserFile::where('owner_id', Auth::user()->getAuthIdentifier())
            ->where('path', $path)
            ->where('type', 'folder')
            ->where('filename', $folderName);

        if ($aimFolder->exists()) {
            $aimFolder->delete();
            return $this->makeReturn(200, 'ok');
        }
        return $this->makeReturn(101, '文件夹不存在');
    }

    public function makeReturn(int $code, string $msg, array $data = []): array
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
