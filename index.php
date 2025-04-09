<?php
// Connect to the database
try {
  $pdo = new PDO("sqlite:todos.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Create table if necessary
  $pdo->query(
    "CREATE TABLE IF NOT EXISTS Todos (id INTEGER PRIMARY KEY, Task TEXT, Complete INTEGER, Created INTEGER, Completed INTEGER)"
  );
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

/// User actions
// Create new todo
if (isset($_POST["new-task"])) {
  try {
    $insert = $pdo->prepare(
      "INSERT INTO Todos (Task, Complete, Created) VALUES (:task, 0, strftime('%s', 'now'))"
    );
    $insert->execute([":task" => $_POST["new-task"]]);
  } catch (PDOException $e) {
    echo "Todo creation failed: " . $e->getMessage();
  }
}

// Edit todo
if (isset($_POST["edit-task"])) {
  try {
    $update = $pdo->prepare(
      "UPDATE Todos SET Task = :task WHERE id = :id"
    );
    $update->execute([":id" => $_POST["id"], ":task" => $_POST['task']]);
    
    header("location: ./");

  } catch (PDOException $e) {
    echo "Todo edit failed: " . $e->getMessage();
  }
}

// Complete todo
if (isset($_POST["complete"])) {
  try {
    $update = $pdo->prepare(
      "UPDATE Todos SET Complete = 1, Completed = strftime('%s', 'now') WHERE id = :id"
    );
    $update->execute([":id" => $_POST["id"]]);
  } catch (PDOException $e) {
    echo "Todo completion failed: " . $e->getMessage();
  }
}

// Uncomplete todo
if (isset($_POST["uncomplete"])) {
  try {
    $update = $pdo->prepare(
      "UPDATE Todos SET Complete = 0, Completed = NULL WHERE id = :id"
    );
    $update->execute([":id" => $_POST["id"]]);
  } catch (PDOException $e) {
    echo "Todo uncompletion failed: " . $e->getMessage();
  }
}

// Delete one todo
if (isset($_POST["delete-one"])) {
  try {
    $delete = $pdo->prepare("DELETE FROM Todos WHERE id = :id");
    $delete->execute([":id" => $_POST["id"]]);
  } catch (PDOException $e) {
    echo "Todo deletion failed: " . $e->getMessage();
  }
}

// Delete all todos
if (isset($_POST["delete-all"])) {
  try {
    $pdo->query("DELETE FROM Todos");
  } catch (PDOException $e) {
    echo "Todo deletion failed: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <!-- prettier-ignore -->
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/7f1bac7050.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Dawning+of+a+New+Day&display=swap" rel="stylesheet" />
    <title>DRIFTSTATUS</title>
  </head>

  <body
    class="d-flex flex-column position-absolute"
    style="min-height: 100%; width: 100%; background-color: #dee2e6;"
  >
    <div class="container-fluid" style="max-width: 720px;">
      <div class="card my-4">
        <div class="card-body">
          <!-- Title -->
          <h1 class="mb-4">DRIFTSTATUS</h1>

          <!-- Input form -->
          <form class="d-flex mb-4" method="POST">
            <input
              type="text"
              class="form-control mr-2"
              name="new-task"
              aria-label="New task"
            />
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus fa-fw"></i></button>
          </form>

          <!-- Task list -->
          <ul class="list-group mb-4">
<?php
$edit_id = 0;

// List incomplete tasks first, then in decending order by date
$select = $pdo->query(
  "SELECT * FROM Todos ORDER BY Complete ASC, Completed DESC, Created DESC"
);

// Find task count while looping
$count = 0;

// Loop through tasks
foreach ($select as $todo) {
  $count++;
  $id = $todo["id"];
  $task = $todo["Task"];
  $complete = $todo["Complete"];
  
  if(isset($_GET['todo'])) {
      $edit_id = $_GET['todo'];
  }
?>
            <li 
              class="list-group-item d-flex justify-content-between align-items-center"
            >
                <?php if($edit_id != $id): ?>
                <?= $complete ? '<del class="text-secondary">' : ''; ?>
                    <?= $task; ?>
                <?= $complete ? '</del>' : ''; ?>
                <?php endif; ?>


              <form
                action=""
                <?php if(isset($edit_id) && $edit_id != $id): ?>
                class="btn-group"
                <?php else: ?>
                class="input-group"
                <?php endif; ?>
                role="group"
                aria-label="Task actions"
                method="POST"
              >
                  
                  <!-- Task -->
                <?php if(isset($edit_id) && $edit_id == $id): ?>
                <input type="text" class="form-control" name="task" value="<?= $task; ?>" />
                <?php endif; ?>
                  
                <!-- Edit buttons -->
                <?php if(isset($edit_id) && $edit_id == $id): ?>
                <button
                  type="submit"
                  name="edit-task"
                  class="btn btn-warning"
                  aria-label="Edit"
                >
                  <i class="fas fa-save fa-fw"></i>
                </button>
                <?php else: ?>
                <a class="btn btn-warning" aria-label="Edit" href="?todo=<?= $id; ?>"><i class="fas fa-pen fa-fw"></i></a>
                <?php endif; ?>
                
                <!-- Complete/delete buttons -->
                <button
                  type="submit"
                  name="<?= $complete ? 'un' : ''; ?>complete"
                  class="btn btn-success<?= $complete ? ' active' : ''; ?>"
                  <?= $complete ? 'aria-pressed="true"' : ''; ?>
                  aria-label="Complete"
                >
                  <i class="fas fa-check fa-fw"></i>
                </button>
                
                <input type="hidden" name="id" value="<?= $id; ?>" />
                <button
                  onClick="return confirm('Are you sure?');"
                  type="submit"
                  name="delete-one"
                  class="btn btn-danger"
                  aria-label="Delete"
                >
                  <i class="fas fa-trash-alt fa-fw"></i>
                </button>
                
              </form>
            </li>
<?php
}
?>
          </ul>

          <!-- Delete all tasks button -->
          <form method="POST">
            <button
              onClick="return confirm('Are you sure?');"
              type="submit"
              name="delete-all"
              class="btn btn-danger px-5 d-block mx-auto"<?= $count === 0 ? ' disabled' : ''; ?>
            >
              DELETE ALL TASKS
            </button>
          </form>
        </div>
      </div>

      
    <!-- Creative Commons license -->
    <footer class="text-center mt-auto mb-3">
      <a
        rel="license"
        href="http://creativecommons.org/licenses/by/4.0/"
        title="Creative Commons Attribution 4.0 International License"
        class="text-reset text-decoration-none"
      >
        <i class="fab fa-creative-commons"></i>&#x0200A;<i
          class="fab fa-creative-commons-by"
        ></i>
      </a>
      2020
      <a
        href="http://edbit.dk"
        xmlns:cc="http://creativecommons.org/ns#"
        property="cc:attributionName"
        rel="cc:attributionURL"
        class="text-reset text-decoration-none"
      >
        EdBit DK
      </a>
    </footer>
  </body>
</html>
