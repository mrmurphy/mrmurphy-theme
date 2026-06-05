# Skill: MrMurphy Theme AI Authorship

This skill teaches how to add AI authorship metadata to posts on the MrMurphy WordPress theme. The theme has a built-in AI Authorship system that displays expandable pill buttons showing who wrote a post (humans, AI models, AI agents, skills, and harnesses used).

## Meta Key

All authorship data is stored as a single JSON post meta field:

- **Meta key:** `_mrmurphy_authorship`
- **Format:** JSON-encoded associative array with category slugs as keys

## Category Structure

Authorship data is organized into categories. Each category contains an array of entries, where each entry has a `name` and optional `link`.

### Default Categories

| Slug | Label (singular) | Label (plural) | Icon | Color |
|------|-------------------|----------------|------|-------|
| `human` | Human | Humans | user | green |
| `model` | AI Model | AI Models | cpu-chip | orange |
| `agent` | AI Agent | AI Agents | sparkles | purple |
| `skill` | Skill | Skills | light-bulb | yellow |
| `harness` | Harness | Harnesses | server | cyan |

### JSON Structure

```json
{
  "human": [
    { "name": "Murphy Randle" }
  ],
  "model": [
    { "name": "Qwen3.6-35B", "link": "https://example.com/qwen" }
  ],
  "agent": [
    { "name": "Claude Code", "link": "https://example.com/claude-code" }
  ],
  "skill": [
    { "name": "brainstorming" },
    { "name": "wp-plugin-development", "link": "https://example.com/skill" }
  ]
}
```

## Adding Authorship to Posts via WP-CLI

### Set authorship on a single post

```bash
studio wp --path /Users/murphy/Studio/murphy-randle post meta update <POST_ID> _mrmurphy_authorship '{"human":[{"name":"Murphy Randle"}]}' --format=json
```

### Set authorship with multiple categories

```bash
studio wp --path /Users/murphy/Studio/murphy-randle post meta update <POST_ID> _mrmurphy_authorship '{
  "human": [{"name": "Murphy Randle"}],
  "model": [{"name": "Qwen3.6-35B"}],
  "agent": [{"name": "Claude Code"}]
}' --format=json
```

### Verify authorship data

```bash
studio wp --path /Users/murphy/Studio/murphy-randle post meta get <POST_ID> _mrmurphy_authorship --format=json
```

### Delete authorship data

```bash
studio wp --path /Users/murphy/Studio/murphy-randle post meta delete <POST_ID> _mrmurphy_authorship
```

### Set authorship on ALL posts (bulk)

```bash
# Get all post IDs
studio wp --path /Users/murphy/Studio/murphy-randle post list --post_type=post --format=ids --number=9999 | xargs -I {} \
  studio wp --path /Users/murphy/Studio/murphy-randle post meta update {} _mrmurphy_authorship '{"human":[{"name":"Murphy Randle"}]}' --format=json
```

### Set authorship on ALL posts with model/agent info (bulk)

```bash
# Get all post IDs
studio wp --path /Users/murphy/Studio/murphy-randle post list --post_type=post --format=ids --number=9999 | xargs -I {} \
  studio wp --path /Users/murphy/Studio/murphy-randle post meta update {} _mrmurphy_authorship '{"human":[{"name":"Murphy Randle"}],"model":[{"name":"Qwen3.6-35B"}],"agent":[{"name":"Claude Code"}]}' --format=json
```

## Authorship System Architecture

The system consists of:

- **`inc/ai-authorship.php`** — Main module, defines constants, loads classes, handles migration
- **`inc/ai-authorship/class-categories.php`** — Category definitions, labels, icons
- **`inc/ai-authorship/class-meta.php`** — Read/write/validate/normalize authorship meta
- **`inc/ai-authorship/class-render.php`** — Frontend rendering (pill button + expandable details)
- **`inc/ai-authorship/class-admin.php`** — Gutenberg sidebar panel + legacy meta box
- **`assets/css/ai-authorship.css`** — Frontend styles
- **`assets/css/ai-authorship-editor.css`** — Editor panel styles
- **`assets/js/ai-authorship.js`** — Frontend pill toggle animation
- **`assets/js/ai-authorship-editor.js`** — Gutenberg sidebar panel

## Important Notes

- The `human` category is the most common — most posts just need `{"human":[{"name":"Author Name"}]}`
- Links are optional per entry; omit the `link` key for entries without URLs
- The meta key is `_mrmurphy_authorship` (underscore prefix = hidden custom field in WordPress admin)
- The rendering is hooked into `single.php` after `the_content()` — it only appears on single post pages
- The category validation only allows registered category slugs (human, model, agent, skill, harness) — using an invalid slug will cause the save to fail
