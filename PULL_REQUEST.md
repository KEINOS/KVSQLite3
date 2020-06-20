
# Pull Request (PR)

- PR to the "`master`" branch.
- The PR won't be reviewd until the CI passes all the tests.
- DO NOT fix too many issues at once.
    - 1 fix per 1 PR is preferable.
    - If too many fixes were made, the PR might be left unattended without notice.
- Follow the coding styles of other script as much as possible.
    - As a regulation you need to follow the PSR-2 coding style with some rules exclued.
    - See: /.phpcs.xml

## Preparation

1. Fork the upstream repo (this repo) to your GitHub account.
2. Clone the forked repo (the origin) above to your local.
3. Create a branch from the "`master`" with an easily-identifiable name.
    - Including the issue number is recommended.
4. Add a brief comment on what you are willing to do near where you plan to change.
5. Push the branch to origin.
6. Before coding, **Pull Request as "Draft"** to the upstream repo's "`master`" branch.<br>
   This "draft pull request" will tell the others what you're planning to do. Due to avoide others' hands into the same issue.<br>In "`draft`" state, the other contributers WILL NOT or SHOULD NOT comment or add changes unitl you clearly comment in the PR to do so. Also you can drop out (close) the Draft PR at any time if you feel any pain in any case, without saying anything. Take a rest.
   - [Introducing draft pull requests](https://github.blog/2019-02-14-introducing-draft-pull-requests/) @ GitHub Blog (EN)
   - [Draft Pull Requestをリリースしました](https://github.blog/jp/2019-02-19-introducing-draft-pull-requests/) @ GitHub Blog (JA)
   - [Solicitudes de extracción en borrador](https://help.github.com/es/github/collaborating-with-issues-and-pull-requests/about-pull-requests#draft-pull-requests) @ GitHub Helo (ES)
7. Once you did PR the draft, the CI should run and pass the test (if not wrongly commented), then start your coding.

## Coding

1. Start coding in the branch of the Draft PR you created above.
2. "`commit`" changes and "`push`" then take a break and see the CI's status.
3. If you need help or advice then:
    - Leave a comment in your PR at GitHub and set the "help wanted" label. Once your problem was solved, don't forget to un-set the label.
    - If you wish or let the other contributors to add changes (push a commit) into your Draft PR then also leave a comment as well.
4. Repeat the 2-3 until you are satisfied to be reviewed.
5. If you are ready fot the review then change the "`draft`" state to "`ready to review`".
6. Leave a comment to tell others for reviewing.
    - If you want the other contributors to add additional commits in your PR:
        - Add: `- Additional commit allowed`

### Review

1. Anyone can review / leave a comment / add commit
