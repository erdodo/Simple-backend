<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Kurulum sayfası</title>
    <?php $this->load->view('layout/style') ?>

</head>

<body>
    <div class="container">

        <div class="row my-5 py-md-5 py-0 align-items-center justify-content-center">
            <div class="col-12 col-md-10 col-xl-8 col-xxl-7">
                <div class="d-flex flex-column">
                    <div>
                        <span>*Veritabanını oluştur.</span><br>
                        <span>*Veritabanı kullanıcı adı ve şifresini database.php dosyasına doldur.</span>
                        <form action="/setup/create" method="get">

                            <label for="">E-posta</label>
                            <input type="email" name="email" require class="form-control mb-2" id="">
                            <label for="">Şifre</label>
                            <input type="password" name="password" require class="form-control mb-2" id="">
                            <button class="btn btn-success">Oluştur</button>
                        </form>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <script>
        export default {
            data() {
                return {
                    db_name: ''
                }
            },
        }
    </script>
    <?php $this->load->view('layout/script') ?>
</body>

</html>