<?php

namespace App\Services;

class MUService
{
    public function authenticate($ticket)
    {
        // TODO: ยิง API ไปที่ MU-SSO (เช่น OAuth2 หรือ SOAP ตามสเปกมหาวิทยาลัย)
        // return $userData; 
    }

    public function syncUser($muData)
    {
        // Logic สำหรับการเช็กว่ามี User นี้ในระบบหรือยัง 
        // ถ้ามีแล้วให้ Update ถ้าไม่มีให้ Create ใหม่
    }
}
