<?php 
session_start();
if(empty($_GET["id"])){
    header("Location: /04-router/");
    exit;
}
require __DIR__."/../../../../ressources/service/_pdo.php";
$messages = $pdo = $sql = $flash = $user = $logged = "";

$pdo = connexionPDO();
// Partie 1 uniquement :
// $sql = $pdo->prepare("SELECT * FROM messages WHERE idUser = ? ORDER BY createdAt DESC");
// $sql->execute([(int)$_GET["id"]]);
// $messages = $sql->fetchAll();
// Fin partie 1.
$sql = $pdo->prepare("SELECT username FROM users WHERE idUser = ?");
$sql->execute([(int)$_GET["id"]]);
$user = $sql->fetch();
// Partie 2 uniquement :
if(empty($_GET["cat"]))
{
    $sql = $pdo->prepare("SELECT m.*, c.name as categorie FROM messages m LEFT JOIN categories c ON m.idCat = c.idCat WHERE m.idUser = ? ORDER BY m.createdAt DESC");
    $sql->execute([(int)$_GET["id"]]);
}
else
{
    $sql = $pdo->prepare("SELECT m.*, c.name as categorie FROM messages m LEFT JOIN categories c ON m.idCat = c.idCat WHERE m.idUser = ? AND m.idCat = ? ORDER BY m.createdAt DESC");
    $sql->execute([
        (int)$_GET["id"],
        (int)$_GET["cat"]
    ]);
}

$messages = $sql->fetchAll();

$sql = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $sql->fetchAll();
// Fin partie 2.

if(isset($_SESSION["flash"])){
    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
}
$logged = isset($_SESSION["idUser"]) && $_GET["id"]==$_SESSION["idUser"];
$title = " Blog de ".$user["username"];
require __DIR__."/../../../../ressources/template/_header.php";
if($flash): ?>
    <div class="flash">
        <?php echo $flash ?>
    </div>
<?php endif; 
if($logged):
?>
<form action="/04-router/blog/nouveau" method="post">
    <textarea name="message" placeholder="Nouveau Message"></textarea>
    <!-- Partie 2 uniquement -->
    <select name="categorie">
        <option value="">Selection de catégorie</option>
        <?php foreach($categories as $cat): ?>
            <option value="<?php echo $cat["idCat"] ?>">
                <?php echo $cat["name"] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <!-- Fin partie 2 -->
    <input type="submit" value="Envoyer" name="addMessage">
</form>
<!-- Partie 2 uniquement -->
<a href="?id=<?php echo $_GET["id"] ?>">
    Toute les catégories.
</a>
<!-- fin partie 2 -->
<?php 
endif;
if($messages):
    foreach($messages as $m):
?>
<div class="message">
    <div class="date1"> 
        Ajouté le <?php echo $m["createdAt"] ?>
    </div>
    <div class="date2">
        <?php echo ($m["editedAt"]?"édité le : ".$m["editedAt"]: "") ?>
        </div>
    <p><?php echo $m["message"] ?></p>
    <div class="btns">
        <!-- Partie 2 uniquement -->
        <?php if(!empty($m["categorie"])): ?>
            <a href="?id=<?php echo $m["idUser"] ?>&cat=<?php echo $m["idCat"] ?>">
                <?php echo $m["categorie"] ?>
            </a>
        <?php endif; ?>
        <!-- Fin partie 2 -->
        <?php if($logged): ?>
            <a href="/04-router/blog/modifier?id=<?php echo $m["idMessage"] ?>">éditer</a>
            <a href="/04-router/blog/supprimer?id=<?php echo $m["idMessage"] ?>">supprimer</a>
        <?php endif; ?>
    </div>
</div>
<?php 
endforeach;
else: 
?>
    <p>Cet utilisateur n'a aucun message</p>
<?php 
endif;
require __DIR__."/../../../../ressources/template/_footer.php";
?>