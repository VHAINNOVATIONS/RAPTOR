using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Runtime.InteropServices;
using System.Runtime.ConstrainedExecution;
using System.Security;
using System.Security.Principal;
using gov.va.medora.mdo.exceptions;
using Microsoft.Win32.SafeHandles;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class IdentityImpersonationUtil
    {
        [DllImport("advapi32.dll", SetLastError = true, CharSet = CharSet.Unicode)]
        public static extern bool LogonUser(String lpszUsername, String lpszDomain, String lpszPassword, int dwLogonType, int dwLogonProvider, out SafeTokenHandle phToken);
        
        [DllImport("kernel32.dll", CharSet = CharSet.Auto)]
        public static extern bool CloseHandle(IntPtr handle);

        public static WindowsIdentity Impersonate(string username, string domain, string password)
        {
            SafeTokenHandle safeTokenHandle;
            try
            {
                const int LOGON32_PROVIDER_DEFAULT = 0;
                const int LOGON32_LOGON_NEW_CREDENTIALS = 9;

                bool impersonated = LogonUser(username, domain, password, LOGON32_LOGON_NEW_CREDENTIALS, LOGON32_PROVIDER_DEFAULT, out safeTokenHandle);
                if (impersonated == false)
                {
                    int errorCode = Marshal.GetLastWin32Error();
                    System.Console.WriteLine("LogonUser Failed: " + errorCode);
                }

                WindowsIdentity identity = new WindowsIdentity(safeTokenHandle.DangerousGetHandle());
                return identity;
            }
            catch (Exception ex)
            {
                Console.WriteLine("Exception occured: " + ex.Message);
                throw new MdoException(MdoExceptionCode.DATA_SOURCE_NON_SPECIFIC_ERROR, "Exception Occured while connecting to the data source");
            }
        }
    }

    public sealed class SafeTokenHandle : SafeHandleZeroOrMinusOneIsInvalid
    {
        private SafeTokenHandle()
            : base(true)
        {
        }

        [DllImport("kernel32.dll")]
        [ReliabilityContract(Consistency.WillNotCorruptState, Cer.Success)]
        [SuppressUnmanagedCodeSecurity]
        [return: MarshalAs(UnmanagedType.Bool)]
        private static extern bool CloseHandle(IntPtr handle);

        protected override bool ReleaseHandle()
        {
            return CloseHandle(handle);
        }
    }
}
