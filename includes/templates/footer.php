<?php 
   /*  use ProtoClase\EnvEmail;
    //Seccion de envio de correos
    $Email = new EnvEmail(); */

?>

    <footer id="fot">
        <div class='formulario'>
            <form class='item1' action='/enviar' method='POST'>
                <label for='comentario'>
                    <p>¿Nos quieres contar algo más?</p>
                </label>
                <textarea class='item2' id='comentario' name='comentario' rows='5' cols='30'
                    placeholder='Escribe aquí tus dudas'></textarea>
                <button class='boton' type='submit'>Enviar</button>
            </form>

            <p class='item2 sincursor'>Todos los derechos reservados Vive Composta <?php echo date('Y'); ?> &copy;</p>
            <div class='item3'>
                <p><a href='/Aviso_VC.pdf' target='_blank'>Aviso de privacidad</a></p>
                <p><a href='/Preguntas frecuentes.pdf' target="_blank">Preguntas frecuentes</a></p>
                <p><a href='#'>Términos y condiciones</a></p>
            </div>

        </div>
    </footer>
</html>