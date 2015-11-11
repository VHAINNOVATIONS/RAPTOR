using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class CachedObject
    {
        public delegate object refreshMethod();

        public DateTime Timestamp { get; set; }
        public DateTime Expires { get; set; }
        public object Cache { get; set; }
        
        /// <summary>
        /// Check to see if the object's expiration period has elapsed
        /// </summary>
        /// <returns>True if the expiration time has passed, false otherwise</returns>
        public bool hasExpired()
        {
            if (DateTime.Now.CompareTo(Expires) >= 0)
            {
                return true;
            }
            return false;
        }

        /// <summary>
        /// Add the goodFor time span to the cached object's timestamp
        /// </summary>
        /// <param name="goodFor">How long the object is good for</param>
        public void setTimeout(TimeSpan goodFor)
        {
            Expires = Timestamp.Add(goodFor);
        }

    }
}
