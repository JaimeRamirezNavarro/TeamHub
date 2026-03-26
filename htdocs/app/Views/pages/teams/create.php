<?php
$error = $error ?? null;
?>
<div style="max-width: 560px; margin: 0 auto;">

    <!-- Header -->
    <div class="content-header" style="margin-bottom: 28px;">
        <div>
            <h1 class="project-title" style="font-size: 1.4rem;">Crear nuevo proyecto</h1>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">Completa la información para registrar un nuevo proyecto en TeamHub.</p>
        </div>
        <a href="<?= BASE_PATH ?>/teams/all"
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; text-decoration:none; font-size:0.8rem; font-weight:500; color:var(--text-muted); transition:all 0.15s;"
           onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)'"
           onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Ver todos
        </a>
    </div>

    <?php if ($error): ?>
    <div style="display:flex; align-items:center; gap:8px; padding:12px 16px; margin-bottom:20px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:12px; color:#dc2626; font-size:0.875rem; font-weight:500;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/></svg>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= BASE_PATH ?>/teams/store" method="POST">

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:var(--text-faint); margin-bottom:8px;">
                    Nombre del proyecto *
                </label>
                <input type="text" name="name" id="name" required
                       placeholder="Ej. Marketing Q1, App iOS…"
                       style="width:100%; padding:11px 16px; border:1px solid var(--border); border-radius:12px; font-size:0.875rem; color:var(--text); background:var(--input-bg); outline:none; transition:box-shadow 0.15s, border-color 0.15s; box-sizing:border-box;"
                       onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.12)'"
                       onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>

            <div style="margin-bottom: 28px;">
                <label style="display:block; font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:var(--text-faint); margin-bottom:8px;">
                    Descripción
                </label>
                <textarea name="description" id="description" rows="4"
                          placeholder="¿De qué trata este proyecto?"
                          style="width:100%; padding:11px 16px; border:1px solid var(--border); border-radius:12px; font-size:0.875rem; color:var(--text); background:var(--input-bg); outline:none; resize:vertical; line-height:1.6; transition:box-shadow 0.15s, border-color 0.15s; box-sizing:border-box;"
                          onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.12)'"
                          onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'"></textarea>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-brand" style="flex:1;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Crear proyecto
                </button>
                <a href="<?= BASE_PATH ?>/teams/all" class="btn" style="background:var(--surface); border:1px solid var(--border); color:var(--text-muted); text-decoration:none;">
                    Cancelar
                </a>
            </div>

        </form>
    </div>

</div>