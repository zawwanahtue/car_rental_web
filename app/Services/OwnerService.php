<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OwnerService
{
    public function getAllOwners() {
        $owners = DB::table('owners')->get();
        if (!$owners) {
            return "No owners found.";
        }
        return $owners;
    }

    public function getOwnerById($id) {
        $owner = DB::table('owners')->where('owner_id', $id)->first();
        if (!$owner) {
            return "No owner found.";
        }
        return $owner;
    }

    public function createOwner(array $data) {
        $response = DB::table('owners')->insert($data);
        if (!$response) {
            return "Failed to create owner.";
        }
        return null;
    }

    public function updateOwner(array $data) {
        $data['updated_at'] = now();
        $response = DB::table('owners')->where('owner_id', $data['owner_id'])->update($data);
        if (!$response) {
            return "Failed to update owner.";
        }
        return null;
    }

    public function deleteOwner($id) {
        $response = DB::table('owners')->where('owner_id', $id)->delete();
        if (!$response) {
            return "Failed to delete owner.";
        }
        return null;
    }
}