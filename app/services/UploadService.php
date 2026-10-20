<?php
declare(strict_types=1);

class UploadService
{
    public const MAX_NAME_LENGTH = 180;

    /**
     * Validate and store an uploaded PDF outside the public directory.
     *
     * @return array{original:string,stored:string,path:string,mime:string,size:int}
     */
    public static function save(array $file): array
    {
        global $config;

        $error = $file["error"] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::errorMessage((int) $error));
        }
        if (!isset($file["tmp_name"], $file["name"], $file["size"]) || !is_uploaded_file($file["tmp_name"])) {
            throw new RuntimeException("O envio do arquivo não foi concluído. Tente novamente.");
        }

        $size = (int) $file["size"];
        if ($size <= 0) {
            throw new RuntimeException("O arquivo enviado está vazio.");
        }
        if ($size > (int) $config["upload_max_bytes"]) {
            throw new RuntimeException(
                "O arquivo excede o limite de " . format_bytes((int) $config["upload_max_bytes"]) . ".",
            );
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]) ?: "";
        if (!isset($config["allowed_mimes"][$mime])) {
            throw new RuntimeException("Apenas arquivos PDF são aceitos.");
        }

        // A MIME check alone can be spoofed; confirm the PDF signature as well.
        $handle = fopen($file["tmp_name"], "rb");
        $signature = $handle ? (string) fread($handle, 5) : "";
        if ($handle) {
            fclose($handle);
        }
        if (!str_starts_with($signature, "%PDF-")) {
            throw new RuntimeException("O arquivo não é um PDF válido.");
        }

        $original = self::sanitizeName((string) $file["name"]);
        $stored = bin2hex(random_bytes(20)) . ".pdf";
        $directory = $config["storage"] . "/uploads";
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new RuntimeException("Não foi possível preparar o armazenamento dos arquivos.");
        }
        $target = $directory . "/" . $stored;
        if (!move_uploaded_file($file["tmp_name"], $target)) {
            throw new RuntimeException("Não foi possível salvar o arquivo.");
        }
        @chmod($target, 0640);

        return [
            "original" => $original,
            "stored" => $stored,
            "path" => "storage/uploads/" . $stored,
            "mime" => $mime,
            "size" => $size,
        ];
    }

    private static function sanitizeName(string $name): string
    {
        $name = trim(basename(str_replace("\\", "/", $name)));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', "", $name) ?: "documento.pdf";
        if ($name === "") {
            $name = "documento.pdf";
        }
        return mb_substr($name, 0, self::MAX_NAME_LENGTH);
    }

    private static function errorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "O arquivo excede o limite permitido pelo servidor.",
            UPLOAD_ERR_PARTIAL => "O envio do arquivo foi interrompido. Tente novamente.",
            UPLOAD_ERR_NO_FILE => "Selecione um arquivo PDF.",
            UPLOAD_ERR_NO_TMP_DIR => "O diretório temporário do servidor não está disponível.",
            UPLOAD_ERR_CANT_WRITE => "O servidor não conseguiu gravar o arquivo.",
            UPLOAD_ERR_EXTENSION => "O envio foi bloqueado pelo servidor.",
            default => "Não foi possível receber o arquivo.",
        };
    }
}
