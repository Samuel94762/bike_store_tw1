        </main>
        <footer>
            <!-- place footer here -->
            <h3 >App Bike Store</h3> 
            <h4>Aplicacion de Tienda de bicicletas y accesorios</h4>
            <p>Copyright 2025 Todos los derechos Reservados</p>
        </footer>
        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
        <!-- Paginación con tablas -->
        <script>
            $(document).ready( function(){
                $("#tabla_id").DataTable({
                    "pageLenght": 3,
                    lengthMenu:[
                        [5,10,15,20],
                        [5,10,15,20],
                    ],
                    "language":{
                        "url":"https:cdn.dataTables.net/plug-ins/1.13.1/i18n/es-ES.json"
                    }
                });
            });
        </script>
    </body>
</html>