---
title: Welcome
layout: home
nav_order: 1
---
# The Personal Vault Project

{: .highlight }
📣 NEW: Join the [discussions on GitHub](https://github.com/dlucian/pvp/discussions) or [PVP Discord Server](https://discord.gg/X88sjgga).

**For some of us, a considerable part of our lives is digital: photos, documents, movies, music, services, navigation, journaling, designs, inspiration, sketches, boarding passes, taxi orders, food menus, communication of various types (chats, discussions, emails), source code, notes, tasks, projects, newspapers, books, quotes, articles, schedules and more**.

That's a lot, right? 👀

If this sounds like you, read on.

Imagine how little of all this you actually own.

If, for whatever reason, Google or Apple or Facebook decides to cancel your account, you loose everything stored there.

You loose a small part of your life.

If loosing your social media posts doesn't sound like much, imagine how would you feel if you lost your childhood photos, you journals, or your work.

And that happens more often than not. Hardware failure, malware, attacks or just human error and your data goes away forever.

**This project aims** not only to prevent all that, but **to leverage modern tools for growth, insights and, hopefully, enhancing wisdom**.

## Project goals

Have everything you own digitally, in your immediate possession in a **secure**, **searchable** and **insightful** manner.

### 1. SECURE: Store your digital life (basically everything) forever.

You favorite music, your personal photos, your notes, your favorite quotes and all your important files, including scanned documents. These are pieces of information that should be able to be stored indefinitely. For 50 or 100 years, especially if you'll still be alive by then.

Imagine it being something you can leave as your digital legacy. Let's call this **"[The Vault]"**.

### 2. SEARCHABLE: Index all your content in a searchable way.

A digital life might be a lot, so this it should be indexed and searchable. Easily find photos taken in July 2015, or your journal entries from when the pandemic started.

This involves importing all the data that you have online into your vault, and indexing it in a way that you can find it later. Let's call this **"[The Index]"**.

### 3. INSIGHTFUL: Leverage AI to make connections and find insights.

Using modern tools like [LlamaIndex] or [LangChain], you can add indexed content to an AI ([LLM]) and then ask it for insights based on _all_ your digital content.

Imagine how easy it would be for an AI to pinpoint trends, patterns, turning points in your life, and how you can enrich your life with a very-personal assistant.

Calling this one **"[The AI]"**.

## What would it look like?

A pluggable note-taking tool might include resources from within your vault that match the subject of the note you're looking at...

![Obsidian concept]({% link /assets/images/Obsidian-screenshot.png %})

Notice the **Related files**, **Related communications**, **Relate resources** and even **Visited websites** in the sidebar.

If you're using a Spotlight-like application such as [Alfred](https://www.alfredapp.com/) or [Raycast](https://www.raycast.com/), you could search your entire archive from within anywhere:

![Alfred Raycast mockup]({% link /assets/images/alfred-raycast.png %})

Imagine having everything at your fingertips, backed up, encrypted and forever yours.

## Structure

![PVP structure]({% link /assets/images/pvp-structure.png %})

This system should not impact the way you use any online service, the most frictionless integration is the PESOS approach[^1], which means you publish everywhere and syndicate to your own site. In our case, our own vault.

From our vault, an indexing service would go through all the files and objects, generating meta information and saving it to the index. This is the place where we can leverage AI to summarize documents and even images, so they will be easier to find.

The indexing service, once having added something to the index, can also send it to the [LLM](https://en.wikipedia.org/wiki/Large_language_model), which trains your AI model with your data.

To use everything you should have multiple options, from a desktop interface where you can send carefully crafted queries with time frames and object types, but you should also be able to pop a question to your AI while you're waiting in line to buy a coffee.

And last but not least, since you own all your stuff, sharing it with your friends should be as simple as possible. And you also get to stop sharing something (like a photo album) with someone.

## Contributing

If you're interested in contributing, **[join the discussions](https://github.com/dlucian/pvp/discussions) on GitHub**.

----
[^1]: [PESOS on Indieweb](https://indieweb.org/PESOS)

[The Vault]: {% link the-vault.md %}
[The Index]: {% link the-index.md %}
[The AI]: {% link the-ai.md %}

[LlamaIndex]: https://github.com/jerryjliu/llama_index
[LangChain]: https://github.com/hwchase17/langchain
[LLM]: https://en.wikipedia.org/wiki/Large_language_model
