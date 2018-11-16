#!/usr/bin/env bash
#send slack notifications with all screenshots obtained
for f in tests/_output/*.fail.png; do
FILENAME=$(basename -- "$f")
echo "FILENAME=$FILENAME"
TESTNAME="${FILENAME%.*}"
echo "TESTNAME=$TESTNAME"
curl -X POST -H 'Content-type: application/json' --data "{
    'attachments': [
        {
            'fallback': 'Failed test screenshot',
            'text': 'Test {$f} Screenshot https://github.com/tatsta/test-scheenshots/tree/${SCREENSHOT_COMMIT_HASH}/${TODAY}/${FILENAME}',
            'color': '#764FA5'
        }
    ]
}"  https://hooks.slack.com/services/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; done


