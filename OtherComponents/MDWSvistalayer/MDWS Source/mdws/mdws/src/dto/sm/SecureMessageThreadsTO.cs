using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.domain.sm;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class SecureMessageThreadsTO : AbstractTO
    {
        public int count;
        public ThreadTO[] messageThreads;

        public SecureMessageThreadsTO() { } 

        public SecureMessageThreadsTO(IList<Thread> threads)
        {
            if (threads == null || threads.Count == 0)
            {
                return;
            }

            count = threads.Count;
            messageThreads = new ThreadTO[count];

            for (int i = 0; i < count; i++)
            {
                messageThreads[i] = new ThreadTO(threads[i]);
            }
        }
    }
}