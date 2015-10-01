using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto
{
    public class BoolTO : AbstractTO
    {
        public bool trueOrFalse;

        /// <summary>
        /// Empty constructor
        /// </summary>
        public BoolTO() { }

        /// <summary>
        /// Constructor assignes tf argument to local trueOrFalse boolean value
        /// </summary>
        /// <param name="tf">True or False</param>
        public BoolTO(bool tf)
        {
            trueOrFalse = tf;
        }

        /// <summary>
        /// Runs Boolean.TryParse on string argument
        /// </summary>
        /// <param name="tf">string 'true' or 'false'</param>
        public BoolTO(string tf)
        {
            bool success = Boolean.TryParse(tf, out trueOrFalse);
            // TBD - check for !success and throw exception??
        }
    }
}