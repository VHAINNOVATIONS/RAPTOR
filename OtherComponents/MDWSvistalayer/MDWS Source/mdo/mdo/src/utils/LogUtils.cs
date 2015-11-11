using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Configuration;
using System.Threading.Tasks;
using System.Collections.Concurrent;

namespace gov.va.medora.utils
{
    public class LogUtils
    {
        private static Int32 MDO_LOG_BUFFER_SIZE = 16;
        private static bool SHOULD_LOG = false;
        private static String LOG_FILE = null;
        private static ConcurrentBag<String> LOG_MSGS = new ConcurrentBag<String>();
        private static readonly object _locker = new object();

        // Singleton
        public static LogUtils getInstance()
        {
            if (_logUtils == null)
            {
                lock (_locker)
                {
                    if (_logUtils == null)
                    {
                        _logUtils = new LogUtils();
                    }
                }
            }
            return _logUtils;
        }
        private static LogUtils _logUtils = null;
        private LogUtils()
        {
            LOG_FILE = "mdo.log";
            if (!String.IsNullOrEmpty(ConfigurationManager.AppSettings["MdoLogFile"]))
            {
                LOG_FILE = ConfigurationManager.AppSettings["MdoLogFile"];
            }
            Boolean.TryParse(ConfigurationManager.AppSettings["LogMdo"], out SHOULD_LOG);
            Int32.TryParse(ConfigurationManager.AppSettings["MdoLogBufferSize"], out MDO_LOG_BUFFER_SIZE);
            new Task(() => _logUtils.logMessages()).Start();
        }


        public void Log(String message)
        {
            if (SHOULD_LOG)
            {
                LOG_MSGS.Add(message);
            }
        }

        // should only be called from singleton constructor
        private void logMessages()
        {
            while (true)
            {
                System.Threading.Thread.Sleep(1 * 1000);
                if (LOG_MSGS.Count > MDO_LOG_BUFFER_SIZE)
                {
                    IList<String> tempRef = LOG_MSGS.ToList();
                    LOG_MSGS = new ConcurrentBag<string>();

                    StringBuilder sb = new StringBuilder();
                    foreach (String s in tempRef)
                    {
                        sb.AppendLine(DateTime.Now.ToString() + " > " + s);
                    }
                    try
                    {
                        gov.va.medora.utils.FileIOUtils.writeToFile(LOG_FILE, sb.ToString(), true);
                    }
                    catch (Exception) { }
                }
            }
        }
    }
}
