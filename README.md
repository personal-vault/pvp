# Personal Vault Project PVP[^1]

😌 Imagine having all your digital life, in your possession, indexed and searchable.
🤖 Now, add AI on top and be able to ask it anything about your life. Find trends, detect habits, find the bright and the dark spots.
🧳 Your digital legacy, completely yours. All your photos, notes, posts, projects, chats, everything.
✨ This is a project I've been pondering for a while now, but actually giving it more thought now. I finally have words on paper.

See the [Discussions section](https://github.com/dlucian/pvp/discussions) to ask, comment, suggest or contribute.

## Table of Contents

- [View documentation](https://dlucian.github.io/pvp/) or [edit it](/docs)

Components:
- [`/storage`](storage/) - ZFS tools
- [`/roadrunner`](roadrunner/) - Metadata extraction & processing tools
- [`/database`](database/) - The relational database
- [`/index`](index/) - _(future)_ Indexing tools
- [`/llm`](llm/) - _(future)_ LLM tools
- [`/viewer`](/viewer) - _(future)_ Web interface

## Development

```shell
# Destroy all conainers + volumes and rebuild from scratch
docker compose down -v && docker compose up --build
```

## Licensing and Attribution

This repository is licensed under the [AGPL License].

The documentation software [Just The Docs] is licensed under MIT. A copy of the license is available in [docs/just-the-docs/LICENSE]

The deployment GitHub Actions workflow is heavily based on GitHub's mixed-party [starter workflows]. A copy of their MIT License is available in [actions/starter-workflows].

----
[^1]: Needs a better name, see [Issue #4](https://github.com/dlucian/pvp/issues/4)
[starter workflows]: https://github.com/actions/starter-workflows/blob/main/pages/jekyll.yml
[actions/starter-workflows]: https://github.com/actions/starter-workflows/blob/main/LICENSE
[AGPL License]: https://www.gnu.org/licenses/agpl-3.0.en.html
[docs/just-the-docs/LICENSE]: docs/just-the-docs/LICENSE
