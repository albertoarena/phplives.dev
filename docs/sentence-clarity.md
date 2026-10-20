# Checklist: sentence-level clarity pass

Adapted 16/10/2026 from Cogway's `docs/sentence-clarity.md`, which grew out of the
albertoarena.it checklist. Loosely based on ASD-STE100 Simplified Technical
English: not full STE, but the same bias toward one idea per sentence. Most
readers are not native English speakers. **A sentence that is grammatical and
still needs a second read to parse is a defect.**

**Run it on every piece of text before it ships**: page copy, entry descriptions,
the README, social copy. **Walking the list is the step. Having read it is not.**
A sentence that felt clear as it was typed is exactly the one that reads twice.

## Look for, and split or rewrite

- [ ] **A word repeated in the same sentence without reason.** Repetition like
      this reads as a typo, not emphasis.
- [ ] **Circular phrasing**, where the second half restates the first in other
      words and adds nothing.
- [ ] **Redundant synonym pairs.** "dropped and discarded". Pick one.
- [ ] **Stacked negatives.** Two negated clauses in one sentence make the reader
      hold both at once. Rewrite each as a single negative, or as a positive.
- [ ] **A contrast bundled with a trailing clause.** "It is not X, it is Y: which
      is why Z." Each half of the contrast gets a sentence, and the explanation
      gets its own.
- [ ] **Elliptical fragments that borrow words from the sentence before.**
      "Restarting does not." Say what does not happen.
- [ ] **A gerund clause smuggling in a second fact.** Two facts, two sentences.
- [ ] **A pronoun whose referent is more than one noun away.** When three things
      could be "it", name the one that is.
- [ ] **Two ideas in one sentence.** If a sentence says what a project is and why
      it matters, it is two sentences.

## What this pass must not touch

- [ ] **A number, a date, a version, a project name or a quote.** This is a
      wording pass. If a split seems to need a fact changed, the split is wrong.
- [ ] **Anything inside backticks or a fenced block.**
- [ ] **The line `PHP delivers. PHP lives!`**, which is quoted exactly.

## Checking that a pass changed no claim

Pull every number, every link target and every inline code span out of the text
before and after, and compare the two sets. **Nothing should be lost.** If a
rewrite changes what a sentence says about a project or a person, it is not a
wording pass any more: check it against the project's own source again.
