<?php echo $__env->make('errors.error', ['code' => 'Your account was banned', 'desc' => 'Possible reasons: using multiple accounts to gain advantage or abusing bugs. Contact support for more info (your id: ' . (auth('sanctum')->guest() ? 'unknown' : auth('sanctum')->user()->_id) . ')'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home/ploi/s.davidkohen.com/resources/views/errors/ban.blade.php ENDPATH**/ ?>