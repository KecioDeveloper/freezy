<?php
require_once "config.php";
require_once "auth.php";

$usuario = obterUsuarioLogado();

$stmtHighlight = $pdo->query("
    SELECT h.title, h.cover_image
    FROM highlights h
    ORDER BY h.sort_order ASC
");
$highlights = $stmtHighlight->fetchAll(PDO::FETCH_ASSOC);

$storyImage = "mega.png";

if ($usuario && !empty($usuario["profile_picture"])) {
    $storyImage = $usuario["profile_picture"];
}

$stmtPosts = $pdo->query("
    SELECT
        p.id,
        p.user_id,
        p.title,
        p.service_role,
        p.description,
        p.price,
        p.rating,
        p.total_reviews,
        u.full_name,
        u.profile_picture,
        i.image_url
    FROM portfolio_posts p
    INNER JOIN users u ON u.id = p.user_id
    LEFT JOIN portfolio_post_images i ON i.post_id = p.id AND i.sort_order = 1
    WHERE p.status = 'ativo'
    ORDER BY p.created_at DESC
");

$posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Freezy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<section class="stories">
  <?php foreach ($highlights as $highlight): ?>
    <?php
      $storyCover = $highlight['cover_image'];

      if ($highlight['title'] === 'Seu Story') {
          $storyCover = $storyImage;
      }
    ?>
    <div class="story">
      <img src="<?= htmlspecialchars($storyCover) ?>" alt="<?= htmlspecialchars($highlight['title']) ?>">
      <span><?= htmlspecialchars($highlight['title']) ?></span>
    </div>
  <?php endforeach; ?>
</section>

<main class="feed" style="flex-direction: column; align-items: center; gap: 30px;">
  <?php if ($posts): ?>
    <?php foreach ($posts as $post): ?>
      <div class="card">
        <div class="card-image">
          <img src="<?= htmlspecialchars($post['image_url'] ?? 'fotp3.jpg') ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>

        <div class="card-content">
          <div class="card-header">
            <img src="<?= htmlspecialchars($post['profile_picture'] ?? 'pimenta.png') ?>" alt="<?= htmlspecialchars($post['full_name']) ?>">
            <div>
              <h3><?= htmlspecialchars($post['full_name']) ?></h3>
              <span><?= htmlspecialchars($post['service_role']) ?></span>
            </div>
          </div>

          <h2><?= htmlspecialchars($post['title']) ?></h2>
          <p><?= htmlspecialchars($post['description']) ?></p>

          <div class="card-meta">
            <strong>R$<?= number_format((float)($post['price'] ?? 0), 2, ',', '.') ?></strong>
            <span>⭐ <?= number_format((float)$post['rating'], 2) ?> (<?= (int)$post['total_reviews'] ?>)</span>
          </div>

          <div class="card-footer">
            <?php if ($usuario): ?>
              <?php if ((int)$usuario["id"] === (int)$post["user_id"]): ?>
                <div class="owner-actions">
                  <a class="btn-outline" href="editar_post.php?id=<?= (int)$post['id'] ?>">Editar</a>

                  <form method="POST" action="apagar_post.php" onsubmit="return confirm('Tem certeza que deseja apagar este post?');">
                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                    <button type="submit" class="btn-danger">Apagar</button>
                  </form>
                </div>
              <?php else: ?>
                <button class="btn-outline" type="button">Salvar</button>
                <a class="btn-primary" href="contratar.php?post_id=<?= (int)$post['id'] ?>">Contratar</a>
              <?php endif; ?>
            <?php else: ?>
              <button class="btn-outline" type="button">Salvar</button>
              <a class="btn-primary" href="login.php">Entrar para contratar</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="card">
      <div class="card-content">
        <h2>Nenhum post encontrado</h2>
        <p>Verifique se você já inseriu os dados iniciais no banco de dados <strong>lapis</strong>.</p>
      </div>
    </div>
  <?php endif; ?>
</main>

</body>
</html>