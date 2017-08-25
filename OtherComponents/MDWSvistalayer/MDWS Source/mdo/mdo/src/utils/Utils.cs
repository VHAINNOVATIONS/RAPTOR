using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Runtime.CompilerServices;
using System.IO;
using gov.va.medora.mdo.exceptions;
/*
 * Author: Steven Weber
 * 
 * The utils is a general class for unclassified utility methods
 */
namespace gov.va.medora.utils
{
    public class Utils
    {
        [MethodImpl(MethodImplOptions.Synchronized)]
        public static string errmsg(string expected, string actual)
        {
            return "Expected " + expected + ", got " + actual;
        }

        [MethodImpl(MethodImplOptions.Synchronized)]
        public static string getResponseFromFile(string filePath, string fileName)
        {
            if (String.IsNullOrEmpty(filePath))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid FilePath");
            }

            if (String.IsNullOrEmpty(fileName))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid FileName");
            }

            return FileIOUtils.readFromFile(Path.Combine(filePath, fileName));
        }
    }
}
