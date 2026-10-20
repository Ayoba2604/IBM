<?php
declare(strict_types=1);
class UploadService
{
    public static function save(array $file): array
    {
        global $config;
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Selecione um arquivo PDF válido.");
        }
        if ($file["size"] > $config["upload_max_bytes"]) {
            throw new RuntimeException("O arquivo excede o limite permitido.");
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);
        if (!isset($config["allowed_mimes"][$mime])) {
            throw new RuntimeException("Apenas arquivos PDF são aceitos.");
        }
        $name = bin2hex(random_bytes(20)) . ".pdf";
        $dir = $config["root"] . "/storage/uploads";
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException(
                "Não foi possível preparar o armazenamento.",
            );
        }
        if (!move_uploaded_file($file["tmp_name"], $dir . "/" . $name)) {
            throw new RuntimeException("Não foi possível salvar o arquivo.");
        }
        return [
            "original" => $file["name"],
            "stored" => $name,
            "path" => "storage/uploads/" . $name,
            "mime" => $mime,
            "size" => (int) $file["size"],
        ];
    }
}
