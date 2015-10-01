using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.pool
{
    public abstract class AbstractTimedResource : AbstractResource
    {
        System.Timers.Timer _timer;
        bool _timedOut = false;

        public void setTimeout(TimeSpan timeout)
        {
            this._timer = new System.Timers.Timer(timeout.TotalMilliseconds);
            this._timer.Elapsed += new System.Timers.ElapsedEventHandler(Timer_Elapsed);
            this._timer.Start();
        }

        void Timer_Elapsed(object sender, System.Timers.ElapsedEventArgs e)
        {
            _timedOut = true;
            _timer.Stop();
            CleanUp();
            _timer.Dispose();
            gov.va.medora.utils.LogUtils.getInstance().Log("Disconnected a cxn based on timeout configuration");
        }

        public override bool isAlive()
        {
            return !_timedOut; // return opposite of timed out
        }

        public void resetTimer()
        {
            if (_timer == null)
            {
                return;
            }
            _timer.Stop();
            _timer.Start();
        }

    }
}
